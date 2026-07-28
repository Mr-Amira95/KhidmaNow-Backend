<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Http\Traits\ApiResponse;
use App\Models\Provider;
use App\Models\ProviderSubCategory;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Provider::with(['user.wallet', 'city']);

        if ($request->filled('is_verified')) {
            $query->where('is_verified', filter_var($request->is_verified, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('is_suspended')) {
            if (filter_var($request->is_suspended, FILTER_VALIDATE_BOOLEAN)) {
                $query->where('suspended_until', '>', now());
            } else {
                $query->notSuspended();
            }
        }
        if ($request->filled('availability_status')) {
            $query->where('availability_status', $request->availability_status);
        }
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('business_name', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%"));
            });
        }

        return $this->paginated(ProviderResource::class, $query->latest());
    }

    public function show(Provider $provider)
    {
        $provider->load(['user.wallet', 'city', 'documents', 'subCategories.subCategory']);
        return $this->success(new ProviderResource($provider));
    }

    public function update(UpdateProviderRequest $request, Provider $provider)
    {
        $data = $request->validated();
        $subCategoryIds = $data['sub_category_ids'] ?? null;
        unset($data['sub_category_ids']);

        DB::transaction(function () use ($provider, $data, $subCategoryIds) {
            $provider->update($data);

            if ($subCategoryIds !== null) {
                $provider->subCategories()->delete();
                foreach ($subCategoryIds as $subCategoryId) {
                    ProviderSubCategory::create([
                        'provider_id' => $provider->id,
                        'sub_category_id' => $subCategoryId,
                    ]);
                }
            }
        });

        $provider->load(['user', 'city', 'documents', 'subCategories.subCategory']);

        return $this->success(new ProviderResource($provider), 'Provider updated successfully.');
    }

    public function verify(Provider $provider)
    {
        $provider->update(['is_verified' => true]);
        return $this->success(new ProviderResource($provider), 'Provider verified successfully.');
    }

    public function unverify(Provider $provider)
    {
        $provider->update(['is_verified' => false]);
        return $this->success(new ProviderResource($provider), 'Provider unverified.');
    }

    public function suspend(Request $request, Provider $provider)
    {
        $request->validate([
            'duration_hours' => 'nullable|integer|min:1',
            'reason'         => 'nullable|string|max:255',
        ]);

        $durationHours = $request->integer('duration_hours')
            ?: (int) (Setting::where('key', 'provider_suspension_duration_hours')->value('value') ?? 72);

        $provider->update([
            'suspended_at'      => now(),
            'suspended_until'   => now()->addHours($durationHours),
            'suspension_reason' => $request->input('reason', 'Suspended by admin.'),
        ]);

        NotificationService::send(
            $provider->user_id,
            'Account Suspended',
            "Your provider account has been suspended for {$durationHours} hours.",
            'system',
            $provider->id
        );

        return $this->success(new ProviderResource($provider), 'Provider suspended successfully.');
    }

    public function unsuspend(Provider $provider)
    {
        $provider->update([
            'suspended_at'      => null,
            'suspended_until'   => null,
            'suspension_reason' => null,
        ]);

        NotificationService::send(
            $provider->user_id,
            'Account Reinstated',
            'Your provider account suspension has been lifted.',
            'system',
            $provider->id
        );

        return $this->success(new ProviderResource($provider), 'Provider unsuspended successfully.');
    }

    public function recordDebtPayment(Request $request, Provider $provider)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = (float) $request->input('amount');

        DB::transaction(function () use ($provider, $amount) {
            $wallet = Wallet::firstOrCreate(['user_id' => $provider->user_id]);
            $wallet->increment('balance', $amount);

            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'type'        => 'credit',
                'amount'      => $amount,
                'source_type' => 'debt_repayment',
                'source_id'   => $provider->id,
            ]);
        });

        NotificationService::send(
            $provider->user_id,
            'Payment Recorded',
            "Your payment of {$amount} toward your outstanding commission has been recorded.",
            'system',
            $provider->id
        );

        return $this->success(new ProviderResource($provider->fresh(['user'])), 'Debt payment recorded successfully.');
    }

    public function destroy(Provider $provider)
    {
        $provider->delete();
        return $this->success([], 'Provider deleted successfully.');
    }
}
