<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\CompanyCliqDetailResource;
use App\Http\Resources\PaymentResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\CompanyCliqDetail;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function checkoutPreview(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer' || (int) $serviceRequest->user_id !== (int) $user->id) {
            return $this->error('You are not allowed to view payment details for this request.', 403);
        }

        $serviceRequest->loadMissing('payment');
        $paymentStatus = $serviceRequest->payment?->status ?? 'unpaid';

        $payable = $serviceRequest->status === 'approved'
            && $paymentStatus === 'unpaid';

        return $this->success([
            'service_request_id' => $serviceRequest->id,
            'title'               => $serviceRequest->title,
            'amount'              => $serviceRequest->price ?? 0,
            'status'              => $serviceRequest->status,
            'payment_status'      => $paymentStatus,
            'payable'             => $payable,
            'payment_methods'     => ['card', 'cash', 'cliq'],
            'cliq_details'        => new CompanyCliqDetailResource(CompanyCliqDetail::firstOrCreate([])),
        ], 'Payment details retrieved.');
    }

    public function checkout(CheckoutRequest $request, ServiceRequest $serviceRequest, StripePaymentService $stripe)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer' || (int) $serviceRequest->user_id !== (int) $user->id) {
            return $this->error('You are not allowed to pay for this request.', 403);
        }

        $serviceRequest->loadMissing('payment');
        $payment = $serviceRequest->payment;

        if ($serviceRequest->status !== 'approved' || ($payment?->status ?? 'unpaid') !== 'unpaid') {
            return $this->error('This request is not ready for checkout.', 422);
        }

        if ($payment) {
            $payment->update([
                'amount'         => $serviceRequest->price ?? 0,
                'payment_method' => $request->payment_method,
                'status'         => 'pending',
            ]);
        } else {
            $payment = Payment::create([
                'user_id'            => $user->id,
                'service_request_id' => $serviceRequest->id,
                'amount'             => $serviceRequest->price ?? 0,
                'payment_method'     => $request->payment_method,
                'status'             => 'pending',
            ]);
        }

        if ($request->payment_method === 'cash') {
            $this->handleCashCheckout($payment, $serviceRequest);
        } elseif ($request->payment_method === 'cliq') {
            $this->handleCliqCheckout($request, $payment);
        } else {
            $this->handleCardCheckout($payment, $stripe);
        }

        return $this->success(new PaymentResource($payment->fresh()), 'Checkout created.', 201);
    }

    private function handleCashCheckout(Payment $payment, ServiceRequest $serviceRequest): void
    {
        $payment->update(['transaction_ref' => 'CASH-' . Str::upper(Str::random(10))]);

        $serviceRequest->loadMissing('provider');
        if ($serviceRequest->provider) {
            NotificationService::send(
                $serviceRequest->provider->user_id,
                'Cash Payment Pending',
                'A cash payment of ' . $payment->amount . ' is awaiting your confirmation for "' . $serviceRequest->title . '".',
                'payment',
                $payment->id
            );
        }
    }

    private function handleCliqCheckout(CheckoutRequest $request, Payment $payment): void
    {
        $path = $this->storeUpload($request->file('receipt'), 'payments/receipts');
        $payment->update([
            'transaction_ref' => 'CLIQ-' . Str::upper(Str::random(10)),
            'receipt_path'    => $path,
        ]);

        $adminIds = User::where('user_type', 'admin')->pluck('id')->all();
        if (!empty($adminIds)) {
            NotificationService::sendBulkPush(
                $adminIds,
                'CliQ Payment Pending Confirmation',
                'A CliQ payment of ' . $payment->amount . ' is awaiting review.',
                'payment',
                $payment->id
            );
        }
    }

    private function handleCardCheckout(Payment $payment, StripePaymentService $stripe): void
    {
        $intent = $stripe->createPaymentIntent((float) $payment->amount, [
            'payment_id'         => (string) $payment->id,
            'service_request_id' => (string) $payment->service_request_id,
        ]);

        $payment->update([
            'transaction_ref'          => $intent->id,
            'stripe_payment_intent_id' => $intent->id,
            'stripe_client_secret'     => $intent->client_secret,
        ]);
    }
}
