<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');
        return [
            'name'      => 'sometimes|string|max:255',
            'phone'     => "sometimes|string|unique:users,phone,{$userId}",
            'email'     => "sometimes|nullable|email|unique:users,email,{$userId}",
            'password'  => 'sometimes|string|min:8',
            'user_type' => 'sometimes|in:customer,provider',
            'status'    => 'sometimes|in:active,inactive,blocked',
            'address'   => 'nullable|string',
        ];
    }
}
