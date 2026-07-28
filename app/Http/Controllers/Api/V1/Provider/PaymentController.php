<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\RejectPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Traits\ApiResponse;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function confirm(Request $request, Payment $payment)
    {
        $error = $this->authorizeCashPayment($request, $payment);
        if ($error) {
            return $error;
        }

        $payment->update(['status' => 'paid', 'paid_at' => now()]);
        $payment->serviceRequest()->update(['payment_status' => 'paid']);
        $payment->recordWalletDebit();

        NotificationService::send(
            $payment->user_id,
            'Cash Payment Confirmed',
            'Your cash payment of ' . $payment->amount . ' has been confirmed by the provider.',
            'payment',
            $payment->id
        );

        return $this->success(new PaymentResource($payment->fresh()), 'Payment confirmed successfully.');
    }

    public function reject(RejectPaymentRequest $request, Payment $payment)
    {
        $error = $this->authorizeCashPayment($request, $payment);
        if ($error) {
            return $error;
        }

        $payment->update([
            'status'            => 'failed',
            'rejection_reason'  => $request->rejection_reason,
        ]);

        NotificationService::send(
            $payment->user_id,
            'Cash Payment Rejected',
            'Your cash payment was not confirmed: ' . $request->rejection_reason,
            'payment',
            $payment->id
        );

        return $this->success(new PaymentResource($payment->fresh()), 'Payment rejected.');
    }

    private function authorizeCashPayment(Request $request, Payment $payment)
    {
        $provider = $request->user()->provider;
        $payment->loadMissing('serviceRequest');

        if (!$provider || !$payment->serviceRequest || (int) $payment->serviceRequest->provider_id !== (int) $provider->id) {
            return $this->error('You are not allowed to act on this payment.', 403);
        }

        if ($payment->payment_method !== 'cash') {
            return $this->error('Only cash payments can be confirmed or rejected here.', 422);
        }

        if ($payment->status !== 'pending') {
            return $this->error("This payment is already '{$payment->status}'.", 422);
        }

        return null;
    }
}
