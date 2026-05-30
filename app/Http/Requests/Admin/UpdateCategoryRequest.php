<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'icon'        => 'nullable|string|max:500',
        ];
    }
}
