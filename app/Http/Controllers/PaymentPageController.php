<?php

namespace App\Http\Controllers;

use App\Models\DebtPayment;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentPageController extends Controller
{
    public function success(Request $request)
    {
        $payment = $this->resolvePayment($request);

        return view('payments.checkout-success', ['payment' => $payment]);
    }

    public function cancel(Request $request)
    {
        $payment = $this->resolvePayment($request);

        return view('payments.checkout-cancel', ['payment' => $payment]);
    }

    private function resolvePayment(Request $request): Payment|DebtPayment|null
    {
        return $request->query('type') === 'debt_payment'
            ? DebtPayment::find($request->query('payment_id'))
            : Payment::find($request->query('payment_id'));
    }
}
