<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePayoutStatusRequest;
use App\Http\Resources\PayoutResource;
use App\Http\Traits\ApiResponse;
use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Payout::with(['provider.user', 'serviceRequest']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        return $this->paginated(PayoutResource::class, $query->latest());
    }

    public function show(Payout $payout)
    {
        $payout->load(['provider.user', 'serviceRequest']);
        return $this->success(new PayoutResource($payout));
    }

    public function updateStatus(UpdatePayoutStatusRequest $request, Payout $payout)
    {
        if ($request->status === 'paid' && $payout->status === 'paid') {
            return $this->error('This payout has already been marked as paid.', 422);
        }

        $data = ['status' => $request->status];
        if ($request->status === 'paid') {
            $data['paid_at'] = now();
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($payout, $request, $data) {
            $oldStatus = $payout->status;
            $payout->update($data);

            if ($request->status === 'paid' && $oldStatus !== 'paid') {
                $provider = $payout->provider;
                if ($provider) {
                    $wallet = \App\Models\Wallet::firstOrCreate(['user_id' => $provider->user_id]);
                    $wallet->decrement('balance', $payout->amount);

                    \App\Models\WalletTransaction::create([
                        'wallet_id'   => $wallet->id,
                        'type'        => 'debit',
                        'amount'      => $payout->amount,
                        'source_type' => 'payout',
                        'source_id'   => $payout->id,
                    ]);

                    \App\Services\NotificationService::send(
                        $provider->user_id,
                        'Payout Processed',
                        'Your payout request of ' . $payout->amount . ' has been marked as paid.',
                        'payment',
                        $payout->id
                    );
                }
            }
        });

        return $this->success(new PayoutResource($payout->load(['provider.user', 'serviceRequest'])), 'Payout status updated.');
    }
}
