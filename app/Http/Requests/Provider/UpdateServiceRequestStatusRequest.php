<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequestStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => 'required|in:approved,rejected,in_progress,completed,cancelled',
            'price'  => 'nullable|numeric|min:0',
        ];
    }
}
