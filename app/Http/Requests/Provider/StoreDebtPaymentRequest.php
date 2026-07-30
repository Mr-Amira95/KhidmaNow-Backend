<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebtPaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:card,cash,cliq',
            'receipt'        => 'required_if:payment_method,cliq|image|max:5120',
        ];
    }
}
