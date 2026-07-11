<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $adminId = $this->route('admin')?->id ?? $this->route('admin');

        return [
            'name'     => 'sometimes|string|max:255',
            'phone'    => "sometimes|string|unique:users,phone,{$adminId}",
            'email'    => "sometimes|nullable|email|unique:users,email,{$adminId}",
            'password' => 'sometimes|string|min:8',
            'status'   => 'sometimes|in:active,inactive,blocked',
            'role_id'  => 'sometimes|integer|exists:roles,id',
        ];
    }
}
