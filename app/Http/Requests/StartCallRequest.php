<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartCallRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'call_type' => 'required|string|in:audio,video',
        ];
    }
}
