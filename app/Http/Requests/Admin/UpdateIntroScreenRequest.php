<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntroScreenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'image'           => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'title_ar'        => 'sometimes|string|max:255',
            'title_en'        => 'sometimes|string|max:255',
            'description_ar'  => 'sometimes|string',
            'description_en'  => 'sometimes|string',
            'order'           => 'nullable|integer|min:0',
            'is_active'       => 'nullable|boolean',
        ];
    }
}
