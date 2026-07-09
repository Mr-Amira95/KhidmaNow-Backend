<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationBidRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'provider_id' => 'required|exists:providers,id',
            'price'       => 'required|numeric|min:0',
            'note'        => 'nullable|string|max:1000',
        ];
    }
}
