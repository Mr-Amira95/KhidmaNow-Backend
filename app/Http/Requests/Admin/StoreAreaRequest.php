<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'city_id'   => 'required|integer|exists:cities,id',
            'name_ar'   => 'required|string|max:255',
            'name_en'   => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
