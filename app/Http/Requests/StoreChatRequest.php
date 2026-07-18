<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        if ($this->user()?->user_type === 'provider') {
            return [
                'customer_id' => 'required|integer|exists:users,id,user_type,customer',
            ];
        }

        return [
            'provider_id' => 'required|integer|exists:providers,id',
        ];
    }
}
