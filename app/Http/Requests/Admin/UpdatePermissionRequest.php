<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $permId = $this->route('permission')?->id ?? $this->route('permission');
        return [
            'name' => 'sometimes|string|max:255',
            'key'  => "sometimes|string|max:255|unique:permissions,key,{$permId}",
        ];
    }
}
