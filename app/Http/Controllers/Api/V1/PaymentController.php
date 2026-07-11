<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Traits\ApiResponse;
use App\Models\Payment;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    use ApiResponse;

    public function checkout(CheckoutRequest $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer' || (int) $serviceRequest->user_id !== (int) $user->id) {
            return $this->error('You are not allowed to pay for this request.', 403);
        }

        if ($serviceRequest->status !== 'approved' || $serviceRequest->payment_status !== 'unpaid') {
            return $this->error('This request is not ready for checkout.', 422);
        }

        $payment = Payment::create([
            'user_id'            => $user->id,
            'service_request_id' => $serviceRequest->id,
            'amount'             => $serviceRequest->price ?? 0,
            'payment_method'     => $request->payment_method,
            'status'             => 'pending',
            'transaction_ref'    => 'MOCK-' . Str::upper(Str::random(12)),
        ]);

        return $this->success(new PaymentResource($payment), 'Checkout created. Confirm to complete payment.', 201);
    }

    public function confirm(Request $request, Payment $payment)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer' || (int) $payment->user_id !== (int) $user->id) {
            return $this->error('You are not allowed to confirm this payment.', 403);
        }

        if ($payment->status !== 'pending') {
            return $this->error("This payment is already '{$payment->status}'.", 422);
        }

        $payment->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        $payment->serviceRequest()->update(['payment_status' => 'paid']);

        $payment->load(['serviceRequest.provider']);

        if ($payment->serviceRequest && $payment->serviceRequest->provider) {
            \App\Services\NotificationService::send(
                $payment->serviceRequest->provider->user_id,
                'Payment Confirmed',
                'Payment of ' . $payment->amount . ' has been confirmed for service request: "' . $payment->serviceRequest->title . '".',
                'payment',
                $payment->id
            );
        }

        return $this->success(new PaymentResource($payment), 'Payment confirmed successfully.');
    }
}
