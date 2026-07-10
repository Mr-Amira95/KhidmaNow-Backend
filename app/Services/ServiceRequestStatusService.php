<?php

namespace App\Services;

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
        'approved'    => ['in_progress'],
        'in_progress' => ['completed'],
        'completed'   => ['confirmed'],
    ];

    public function transition(ServiceRequest $serviceRequest, string $toStatus, User $changedBy): ServiceRequest
    {
        $fromStatus = $serviceRequest->status;
        $allowed = self::TRANSITIONS[$fromStatus] ?? [];

        if (!in_array($toStatus, $allowed, true)) {
            throw new InvalidArgumentException("Cannot move a request from '{$fromStatus}' to '{$toStatus}'.");
        }

        if ($toStatus === 'in_progress' && $serviceRequest->payment_status !== 'paid') {
            throw new InvalidArgumentException('The invoice must be paid before the request can start.');
        }

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
                $price = floatval($serviceRequest->price ?? 0);
                
                $commissionRateSetting = Setting::where('key', 'commission_rate')->first();
                $commissionRate = $commissionRateSetting ? floatval($commissionRateSetting->value) : 15.0;
                
                $commission = $price * ($commissionRate / 100.0);
                $netAmount = $price - $commission;
                
                $provider = $serviceRequest->provider;
                
                if ($provider) {
                    Payout::create([
                        'provider_id'        => $provider->id,
                        'service_request_id' => $serviceRequest->id,
                        'amount'             => $netAmount,
                        'commission'         => $commission,
                        'status'             => 'pending',
                    ]);
                    
                    $wallet = Wallet::firstOrCreate(['user_id' => $provider->user_id]);
                    
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
        });

        // Notify the other participant
        $notifyUserId = null;
        if ($changedBy->id === $serviceRequest->user_id) {
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
}
