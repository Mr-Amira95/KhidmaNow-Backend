<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_id'     => 'nullable|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'title'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'nullable|numeric|min:0',
            'note'            => 'nullable|string',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'address'         => 'nullable|string|max:255',
            'scheduled_at'    => 'nullable|date',
            'attachments'     => 'nullable|array',
            'attachments.*'   => 'file|mimes:jpg,jpeg,png,webp,mp4,mov,pdf,doc,docx|max:10240',
        ];
    }
}
