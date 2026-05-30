<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'name'        => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'icon'        => 'nullable|string|max:500',
        ];
    }
}
