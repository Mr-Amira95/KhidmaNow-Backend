<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntroScreenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'image'           => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'title_ar'        => 'required|string|max:255',
            'title_en'        => 'required|string|max:255',
            'description_ar'  => 'required|string',
            'description_en'  => 'required|string',
            'order'           => 'nullable|integer|min:0',
            'is_active'       => 'nullable|boolean',
        ];
    }
}
