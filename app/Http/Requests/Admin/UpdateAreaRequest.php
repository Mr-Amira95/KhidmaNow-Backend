<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'city_id'   => 'sometimes|integer|exists:cities,id',
            'name_ar'   => 'sometimes|string|max:255',
            'name_en'   => 'sometimes|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
