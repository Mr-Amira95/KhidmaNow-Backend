<?php

namespace App\Http\Requests;

use App\Models\Provider;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequestRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'provider_id'     => [
                'required', 'integer', 'exists:providers,id',
                function ($attribute, $value, $fail) {
                    if (Provider::find($value)?->isSuspended()) {
                        $fail('This provider is currently unavailable for new requests.');
                    }
                },
            ],
            'title'           => 'nullable|string|max:255',
            'description'     => 'nullable|string',
            'price'           => 'nullable|numeric|min:0',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'address'         => 'nullable|string|max:255',
            'note'            => 'nullable|string|max:255',
            'scheduled_at'    => 'nullable|date',
            'attachments'     => 'nullable|array',
            'attachments.*'   => 'file|mimes:jpg,jpeg,png,webp,mp4,mov,pdf,doc,docx|max:10240',
        ];
    }
}
