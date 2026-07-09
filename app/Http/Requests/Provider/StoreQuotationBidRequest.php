<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationBidRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'price' => 'required|numeric|min:0',
            'note'  => 'nullable|string|max:1000',
        ];
    }
}
