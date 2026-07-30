<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreDebtPaymentRequest;
use App\Http\Resources\CompanyCliqDetailResource;
use App\Http\Resources\DebtPaymentResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\CompanyCliqDetail;
use App\Models\DebtPayment;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DebtPaymentController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function preview(Request $request)
    {
        $provider = $request->user()->provider;

        return $this->success([
            'debt_amount'      => $provider->debtAmount(),
            'payment_methods'  => ['card', 'cash', 'cliq'],
            'cliq_details'     => new CompanyCliqDetailResource(CompanyCliqDetail::firstOrCreate([])),
        ], 'Debt payment details retrieved.');
    }

    public function index(Request $request)
    {
        $provider = $request->user()->provider;

        return $this->paginated(
            DebtPaymentResource::class,
            DebtPayment::where('provider_id', $provider->id)->latest()
        );
    }

    public function show(Request $request, DebtPayment $debtPayment)
    {
        $provider = $request->user()->provider;
        if (!$provider || (int) $debtPayment->provider_id !== (int) $provider->id) {
            return $this->error('You are not allowed to view this payment.', 403);
        }

        return $this->success(new DebtPaymentResource($debtPayment));
    }

    public function store(StoreDebtPaymentRequest $request, StripePaymentService $stripe)
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return $this->error('Only providers can submit debt payments.', 403);
        }

        // Card payments stay 'unpaid' until Stripe confirms them via webhook — there is no
        // in-between "pending" hold, so an abandoned/incomplete checkout never blocks retries.
        $initialStatus = $request->payment_method === 'card' ? 'unpaid' : 'pending';

        $debtPayment = DebtPayment::create([
            'provider_id'    => $provider->id,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'status'         => $initialStatus,
        ]);

        if ($request->payment_method === 'cash') {
            $this->handleCashSubmission($debtPayment);
        } elseif ($request->payment_method === 'cliq') {
            $this->handleCliqSubmission($request, $debtPayment);
        } else {
            $this->handleCardSubmission($debtPayment, $stripe);
        }

        return $this->success(new DebtPaymentResource($debtPayment->fresh()), 'Debt payment submitted.', 201);
    }

    private function handleCashSubmission(DebtPayment $debtPayment): void
    {
        $debtPayment->update(['transaction_ref' => 'CASH-' . Str::upper(Str::random(10))]);

        $this->notifyAdmins($debtPayment, 'Cash Debt Payment Pending Confirmation');
    }

    private function handleCliqSubmission(StoreDebtPaymentRequest $request, DebtPayment $debtPayment): void
    {
        $path = $this->storeUpload($request->file('receipt'), 'debt-payments/receipts');
        $debtPayment->update([
            'transaction_ref' => 'CLIQ-' . Str::upper(Str::random(10)),
            'receipt_path'    => $path,
        ]);

        $this->notifyAdmins($debtPayment, 'CliQ Debt Payment Pending Confirmation');
    }

    private function handleCardSubmission(DebtPayment $debtPayment, StripePaymentService $stripe): void
    {
        $successUrl = route('payments.checkout.success') . '?type=debt_payment&payment_id=' . $debtPayment->id . '&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('payments.checkout.cancel') . '?type=debt_payment&payment_id=' . $debtPayment->id;

        $session = $stripe->createCheckoutSession(
            (float) $debtPayment->amount,
            'Debt repayment for provider #' . $debtPayment->provider_id,
            $successUrl,
            $cancelUrl,
            [
                'debt_payment_id' => (string) $debtPayment->id,
            ]
        );

        $debtPayment->update([
            'transaction_ref'            => $session->id,
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_intent_id'   => $session->payment_intent,
            'stripe_checkout_url'        => $session->url,
        ]);
    }

    private function notifyAdmins(DebtPayment $debtPayment, string $title): void
    {
        $adminIds = User::where('user_type', 'admin')->pluck('id')->all();
        if (!empty($adminIds)) {
            NotificationService::sendBulkPush(
                $adminIds,
                $title,
                'A debt payment of ' . $debtPayment->amount . ' is awaiting review.',
                'payment',
                $debtPayment->id
            );
        }
    }
}
