<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'city_id'            => 'sometimes|integer|exists:cities,id',
            'business_name'      => 'sometimes|string|max:255',
            'description'        => 'nullable|string',
            'experience_years'   => 'nullable|integer|min:0',
            'sub_category_ids'   => 'nullable|array',
            'sub_category_ids.*' => 'integer|exists:sub_categories,id',
        ];
    }
}
