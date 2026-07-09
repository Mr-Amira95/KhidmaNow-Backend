<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'service_request_id' => 'required|exists:service_requests,id',
            'rater_id'           => 'required|exists:users,id',
            'ratee_id'           => 'required|exists:users,id',
            'rating_type'        => 'required|in:provider,customer',
            'rate'               => 'required|numeric|min:1|max:5',
            'feedback'           => 'nullable|string',
        ];
    }
}
