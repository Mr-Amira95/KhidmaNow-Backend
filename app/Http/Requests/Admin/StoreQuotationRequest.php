<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'         => 'required|exists:users,id',
            'category_id'     => 'nullable|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'title'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'budget'          => 'nullable|numeric|min:0',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'address'         => 'nullable|string|max:255',
            'scheduled_at'    => 'nullable|date',
        ];
    }
}
