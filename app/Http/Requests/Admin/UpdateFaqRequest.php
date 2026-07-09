<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'question_ar' => 'sometimes|string|max:255',
            'question_en' => 'sometimes|string|max:255',
            'answer_ar'   => 'sometimes|string',
            'answer_en'   => 'sometimes|string',
            'order'       => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ];
    }
}
