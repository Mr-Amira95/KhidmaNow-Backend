<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name_ar'        => 'sometimes|string|max:255',
            'name_en'        => 'sometimes|string|max:255',
            'description_ar' => 'sometimes|string',
            'description_en' => 'sometimes|string',
            'icon'           => $this->hasFile('icon') ? 'image|mimes:jpeg,png,jpg,svg|max:2048' : 'nullable|string|max:500',
            'is_active'      => 'nullable|boolean',
        ];
    }
}
