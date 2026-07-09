<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id'    => 'required|integer|exists:categories,id',
            'name_ar'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'icon'           => $this->hasFile('icon') ? 'image|mimes:jpeg,png,jpg,svg|max:2048' : 'nullable|string|max:500',
            'is_active'      => 'nullable|boolean',
        ];
    }
}
