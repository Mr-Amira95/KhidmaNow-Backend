<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Traits\ApiResponse;
use App\Models\Payment;
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
}
