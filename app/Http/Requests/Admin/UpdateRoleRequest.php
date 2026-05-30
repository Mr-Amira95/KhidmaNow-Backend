<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id ?? $this->route('role');
        return [
            'name'             => "sometimes|string|max:255|unique:roles,name,{$roleId}",
            'permission_ids'   => 'nullable|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ];
    }
}
