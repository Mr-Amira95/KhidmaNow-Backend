<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'rate'     => 'required|numeric|min:1|max:5',
            'feedback' => 'nullable|string',
        ];
    }
}
