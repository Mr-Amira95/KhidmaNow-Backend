<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Http\Resources\DebtPaymentResource;
use App\Http\Traits\ApiResponse;
use App\Models\DebtPayment;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DebtPaymentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = DebtPayment::with('provider.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        return $this->paginated(DebtPaymentResource::class, $query->latest());
    }

    public function show(DebtPayment $debtPayment)
    {
        $debtPayment->load('provider.user');
        return $this->success(new DebtPaymentResource($debtPayment));
    }

    public function confirm(DebtPayment $debtPayment)
    {
        $error = $this->authorizeManualConfirmation($debtPayment);
        if ($error) {
            return $error;
        }

        $debtPayment->update(['status' => 'paid', 'paid_at' => now()]);
        $debtPayment->recordWalletCredit();

        NotificationService::send(
            $debtPayment->provider->user_id,
            'Debt Payment Confirmed',
            'Your payment of ' . $debtPayment->amount . ' toward your outstanding balance has been confirmed.',
            'payment',
            $debtPayment->id
        );

        return $this->success(new DebtPaymentResource($debtPayment->fresh()), 'Debt payment confirmed successfully.');
    }

    public function reject(RejectPaymentRequest $request, DebtPayment $debtPayment)
    {
        $error = $this->authorizeManualConfirmation($debtPayment);
        if ($error) {
            return $error;
        }

        $debtPayment->update([
            'status'           => 'failed',
            'rejection_reason' => $request->rejection_reason,
        ]);

        DebtPayment::create([
            'provider_id' => $debtPayment->provider_id,
            'amount'      => $debtPayment->amount,
            'status'      => 'unpaid',
        ]);

        NotificationService::send(
            $debtPayment->provider->user_id,
            'Debt Payment Rejected',
            'Your debt payment was not confirmed: ' . $request->rejection_reason,
            'payment',
            $debtPayment->id
        );

        return $this->success(new DebtPaymentResource($debtPayment->fresh()), 'Debt payment rejected.');
    }

    private function authorizeManualConfirmation(DebtPayment $debtPayment)
    {
        if (!in_array($debtPayment->payment_method, ['cash', 'cliq'], true)) {
            return $this->error('Only cash or CliQ debt payments can be confirmed or rejected here.', 422);
        }

        if ($debtPayment->status !== 'pending') {
            return $this->error("This payment is already '{$debtPayment->status}'.", 422);
        }

        return null;
    }
}
