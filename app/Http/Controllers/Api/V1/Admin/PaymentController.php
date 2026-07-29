<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Traits\ApiResponse;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Payment::with(['user', 'serviceRequest']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return $this->paginated(PaymentResource::class, $query->latest());
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'serviceRequest.provider.user']);
        return $this->success(new PaymentResource($payment));
    }

    public function confirm(Payment $payment)
    {
        $error = $this->authorizeCliqPayment($payment);
        if ($error) {
            return $error;
        }

        $payment->update(['status' => 'paid', 'paid_at' => now()]);
        $payment->recordWalletDebit();

        $payment->load('serviceRequest.provider');

        NotificationService::send(
            $payment->user_id,
            'CliQ Payment Confirmed',
            'Your CliQ payment of ' . $payment->amount . ' has been confirmed.',
            'payment',
            $payment->id
        );

        if ($payment->serviceRequest && $payment->serviceRequest->provider) {
            NotificationService::send(
                $payment->serviceRequest->provider->user_id,
                'Payment Confirmed',
                'Payment of ' . $payment->amount . ' has been confirmed for service request: "' . $payment->serviceRequest->title . '".',
                'payment',
                $payment->id
            );
        }

        return $this->success(new PaymentResource($payment->fresh()), 'Payment confirmed successfully.');
    }

    public function reject(RejectPaymentRequest $request, Payment $payment)
    {
        $error = $this->authorizeCliqPayment($payment);
        if ($error) {
            return $error;
        }

        $payment->update([
            'status'           => 'failed',
            'rejection_reason' => $request->rejection_reason,
        ]);

        Payment::create([
            'user_id'            => $payment->user_id,
            'service_request_id' => $payment->service_request_id,
            'amount'             => $payment->amount,
            'status'             => 'unpaid',
        ]);

        NotificationService::send(
            $payment->user_id,
            'CliQ Payment Rejected',
            'Your CliQ payment was not confirmed: ' . $request->rejection_reason,
            'payment',
            $payment->id
        );

        return $this->success(new PaymentResource($payment->fresh()), 'Payment rejected.');
    }

    private function authorizeCliqPayment(Payment $payment)
    {
        if ($payment->payment_method !== 'cliq') {
            return $this->error('Only CliQ payments can be confirmed or rejected here.', 422);
        }

        if ($payment->status !== 'pending') {
            return $this->error("This payment is already '{$payment->status}'.", 422);
        }

        return null;
    }
}
