<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\NotificationService;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, StripePaymentService $stripe)
    {
        try {
            $event = $stripe->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', '')
            );
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid webhook payload.'], Response::HTTP_BAD_REQUEST);
        }

        $intent = $event->data->object;

        if ($event->type === 'payment_intent.succeeded') {
            $this->markPaid($intent->id);
        } elseif ($event->type === 'payment_intent.payment_failed') {
            $this->markFailed($intent->id, $intent->last_payment_error->message ?? 'Card payment failed.');
        }

        return response()->json(['received' => true]);
    }

    private function markPaid(string $paymentIntentId): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->where('status', 'pending')->first();
        if (!$payment) {
            return;
        }

        $payment->update(['status' => 'paid', 'paid_at' => now()]);
        $payment->recordWalletDebit();
        $payment->load('serviceRequest.provider');

        NotificationService::send(
            $payment->user_id,
            'Payment Confirmed',
            'Your card payment of ' . $payment->amount . ' has been confirmed.',
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
    }

    private function markFailed(string $paymentIntentId, string $reason): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->where('status', 'pending')->first();
        if (!$payment) {
            return;
        }

        $payment->update(['status' => 'failed', 'rejection_reason' => $reason]);

        Payment::create([
            'user_id'            => $payment->user_id,
            'service_request_id' => $payment->service_request_id,
            'amount'             => $payment->amount,
            'status'             => 'unpaid',
        ]);

        NotificationService::send(
            $payment->user_id,
            'Payment Failed',
            'Your card payment could not be completed: ' . $reason,
            'payment',
            $payment->id
        );
    }
}
