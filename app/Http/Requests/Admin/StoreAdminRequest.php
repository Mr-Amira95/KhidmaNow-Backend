<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'phone'    => 'required|string|unique:users,phone',
            'email'    => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8',
            'status'   => 'nullable|in:active,inactive,blocked',
            'role_id'  => 'required|integer|exists:roles,id',
        ];
    }
}
