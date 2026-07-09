<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'message' => 'required_without:attachment|nullable|string',
            'attachment' => 'required_without:message|nullable|file|mimes:jpg,jpeg,png,webp,mp3,wav,m4a,aac,ogg|max:10240',
        ];
    }
}
