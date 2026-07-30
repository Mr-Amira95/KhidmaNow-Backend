<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestTrack;
use App\Models\User;
use InvalidArgumentException;

use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use App\Models\Setting;
use App\Models\Payout;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class ServiceRequestStatusService
{
    private const TRANSITIONS = [
        'pending'     => ['approved', 'rejected', 'cancelled'],
        'approved'    => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed'   => ['confirmed'],
    ];

    public function transition(ServiceRequest $serviceRequest, string $toStatus, User $changedBy): ServiceRequest
    {
        $fromStatus = $serviceRequest->status;
        $allowed = self::TRANSITIONS[$fromStatus] ?? [];

        if (!in_array($toStatus, $allowed, true)) {
            throw new InvalidArgumentException("Cannot move a request from '{$fromStatus}' to '{$toStatus}'.");
        }

        $serviceRequest->loadMissing('payment');
        if ($toStatus === 'in_progress' && $serviceRequest->payment?->status !== 'paid') {
            throw new InvalidArgumentException('The invoice must be paid before the request can start.');
        }

        $wasDebtSuspended = $toStatus === 'confirmed' && $serviceRequest->provider
            ? $serviceRequest->provider->isDebtSuspended()
            : false;

        DB::transaction(function () use ($serviceRequest, $fromStatus, $toStatus, $changedBy) {
            $serviceRequest->update(['status' => $toStatus]);

            ServiceRequestTrack::create([
                'service_request_id' => $serviceRequest->id,
                'from_status'        => $fromStatus,
                'to_status'          => $toStatus,
                'changed_by'         => $changedBy->id,
                'date_time'          => now(),
            ]);

            // Handle payout and wallet credits upon customer confirmation
            if ($toStatus === 'confirmed') {
                // Commission is always calculated from the full request price.
                $price = floatval($serviceRequest->price ?? 0);

                $commissionRate = $this->resolveCommissionRate($serviceRequest);

                $commission = $price * ($commissionRate / 100.0);
                $netAmount = $price - $commission;

                $provider = $serviceRequest->provider;
                $paymentMethod = $serviceRequest->payment?->payment_method;

                if ($provider) {
                    $wallet = Wallet::firstOrCreate(['user_id' => $provider->user_id]);

                    if ($paymentMethod === 'cash') {
                        // The customer already paid the provider directly in cash, so the
                        // company has nothing to pay out — the provider instead owes the
                        // company its commission, tracked as a negative wallet balance.
                        // We still log the full cash amount collected so the provider's
                        // wallet history shows they received it, without affecting the
                        // balance (which must keep reflecting only commission owed).
                        WalletTransaction::create([
                            'wallet_id'   => $wallet->id,
                            'type'        => 'credit',
                            'amount'      => $price,
                            'source_type' => 'cash',
                            'source_id'   => $serviceRequest->id,
                        ]);

                        if ($commission > 0) {
                            $wallet->decrement('balance', $commission);
                            WalletTransaction::create([
                                'wallet_id'   => $wallet->id,
                                'type'        => 'debit',
                                'amount'      => $commission,
                                'source_type' => 'commission',
                                'source_id'   => $serviceRequest->id,
                            ]);
                        }
                    } else {
                        Payout::create([
                            'provider_id'        => $provider->id,
                            'service_request_id' => $serviceRequest->id,
                            'amount'             => $netAmount,
                            'commission'         => $commission,
                            'status'             => 'pending',
                        ]);

                        // Credit gross amount
                        $wallet->increment('balance', $price);
                        WalletTransaction::create([
                            'wallet_id'   => $wallet->id,
                            'type'        => 'credit',
                            'amount'      => $price,
                            'source_type' => 'payment',
                            'source_id'   => $serviceRequest->id,
                        ]);

                        // Debit commission
                        if ($commission > 0) {
                            $wallet->decrement('balance', $commission);
                            WalletTransaction::create([
                                'wallet_id'   => $wallet->id,
                                'type'        => 'debit',
                                'amount'      => $commission,
                                'source_type' => 'commission',
                                'source_id'   => $serviceRequest->id,
                            ]);
                        }
                    }
                }
            }
        });

        if ($toStatus === 'confirmed' && $serviceRequest->provider) {
            $provider = $serviceRequest->provider->fresh();
            if (!$wasDebtSuspended && $provider->isDebtSuspended()) {
                NotificationService::send(
                    $provider->user_id,
                    'Account Suspended',
                    'Your provider account has been suspended for outstanding commission owed to the company. It will be reinstated once you settle the balance.',
                    'system',
                    $provider->id
                );
            }
        }

        if ($toStatus === 'rejected' && $serviceRequest->provider && (int) $changedBy->id === (int) $serviceRequest->provider->user_id) {
            $this->maybeSuspendProvider($serviceRequest->provider);
        }

        if ($toStatus === 'cancelled' && $serviceRequest->provider && (int) $changedBy->id === (int) $serviceRequest->provider->user_id) {
            $this->maybeSuspendProviderForCancellations($serviceRequest->provider);
        }

        // Notify the other participant
        $notifyUserId = null;
        if ((int) $changedBy->id === (int) $serviceRequest->user_id) {
            $notifyUserId = $serviceRequest->provider ? $serviceRequest->provider->user_id : null;
        } else {
            $notifyUserId = $serviceRequest->user_id;
        }

        if ($notifyUserId) {
            $formattedStatus = str_replace('_', ' ', $toStatus);
            NotificationService::send(
                $notifyUserId,
                'Service Request ' . ucfirst($formattedStatus),
                'The service request "' . $serviceRequest->title . '" status has been updated to ' . $formattedStatus . '.',
                'service_request',
                $serviceRequest->id
            );
        }

        return $serviceRequest;
    }

    private function resolveCommissionRate(ServiceRequest $serviceRequest): float
    {
        $category = $serviceRequest->quotation?->category ?? $serviceRequest->provider?->category;

        if ($category && $category->commission_rate !== null) {
            return floatval($category->commission_rate);
        }

        $commissionRateSetting = Setting::where('key', 'commission_rate')->first();

        return $commissionRateSetting ? floatval($commissionRateSetting->value) : 15.0;
    }

    private function resolveIntSetting(string $key, int $default): int
    {
        $setting = Setting::where('key', $key)->first();

        return $setting ? (int) $setting->value : $default;
    }

    private function maybeSuspendProvider(Provider $provider): void
    {
        $limit = $this->resolveIntSetting('provider_rejection_limit', 3);
        $windowHours = $this->resolveIntSetting('provider_rejection_window_hours', 24);
        $durationHours = $this->resolveIntSetting('provider_suspension_duration_hours', 72);

        $rejectionCount = ServiceRequestTrack::where('to_status', 'rejected')
            ->where('changed_by', $provider->user_id)
            ->where('created_at', '>=', now()->subHours($windowHours))
            ->count();

        if ($rejectionCount < $limit) {
            return;
        }

        $provider->update([
            'suspended_at'      => now(),
            'suspended_until'   => now()->addHours($durationHours),
            'suspension_reason' => "Exceeded {$limit} rejected requests within {$windowHours}h.",
        ]);

        NotificationService::send(
            $provider->user_id,
            'Account Suspended',
            "Your provider account has been suspended for {$durationHours} hours after rejecting too many requests.",
            'system',
            $provider->id
        );
    }

    private function maybeSuspendProviderForCancellations(Provider $provider): void
    {
        $limit = $this->resolveIntSetting('provider_cancellation_limit', 3);
        $windowDays = $this->resolveIntSetting('provider_cancellation_window_days', 7);

        $cancellationCount = ServiceRequestTrack::where('to_status', 'cancelled')
            ->where('changed_by', $provider->user_id)
            ->where('created_at', '>=', now()->subDays($windowDays))
            ->count();

        if ($cancellationCount < $limit) {
            return;
        }

        $provider->update([
            'suspended_at'      => now(),
            'suspended_until'   => null,
            'suspension_reason' => "Exceeded {$limit} cancelled requests within {$windowDays} days. Suspended until reviewed by an admin.",
        ]);

        NotificationService::send(
            $provider->user_id,
            'Account Suspended',
            "Your provider account has been suspended after cancelling too many requests. An admin will review your account before it can be reinstated.",
            'system',
            $provider->id
        );
    }
}
