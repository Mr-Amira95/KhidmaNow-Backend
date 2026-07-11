<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;

class AgoraTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Agora channel names: max 64 bytes, restricted ASCII charset.
            'channel_name' => ['required', 'string', 'max:64', 'regex:/^[a-zA-Z0-9 !#$%&()+\-:;<=.>?@\[\]^_{}|~,]+$/'],
            'uid' => ['nullable', 'integer', 'min:0'],
            'user_account' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $validator) {
            if ($this->filled('uid') && $this->filled('user_account')) {
                $validator->errors()->add('user_account', 'Provide either uid or user_account, not both.');
            }
        });
    }
}
