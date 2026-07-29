<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentPageController extends Controller
{
    public function success(Request $request)
    {
        $payment = Payment::find($request->query('payment_id'));

        return view('payments.checkout-success', ['payment' => $payment]);
    }

    public function cancel(Request $request)
    {
        $payment = Payment::find($request->query('payment_id'));

        return view('payments.checkout-cancel', ['payment' => $payment]);
    }
}
