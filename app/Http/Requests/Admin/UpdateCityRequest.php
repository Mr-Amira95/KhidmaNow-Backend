<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'country_id' => 'sometimes|integer|exists:countries,id',
            'name'       => 'sometimes|string|max:255',
            'is_active'  => 'nullable|boolean',
        ];
    }
}
