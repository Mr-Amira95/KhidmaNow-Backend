<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'         => 'required|exists:users,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'title'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'nullable|numeric|min:0',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'address'         => 'nullable|string|max:255',
            'note'            => 'nullable|string|max:255',
            'scheduled_at'    => 'nullable|date',
        ];
    }
}
