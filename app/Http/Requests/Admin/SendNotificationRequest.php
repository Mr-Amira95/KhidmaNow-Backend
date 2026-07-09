<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'    => 'required|string|max:255',
            'body'     => 'required|string',
            'icon'     => 'nullable|string|max:255',
            'type'     => 'required|in:service_request,payment,chat,system',
            'type_id'  => 'nullable|integer',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ];
    }
}
