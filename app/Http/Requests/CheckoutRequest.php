<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:card,cash,cliq',
            'receipt'        => 'required_if:payment_method,cliq|image|max:5120',
        ];
    }
}
