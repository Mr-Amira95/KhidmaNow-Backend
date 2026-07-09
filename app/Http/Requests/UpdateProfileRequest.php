<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'                  => 'sometimes|string|max:255',
            'phone'                 => "sometimes|string|unique:users,phone,{$userId}",
            'email'                 => "sometimes|nullable|email|unique:users,email,{$userId}",
            'profile_image'         => $this->hasFile('profile_image') ? 'image|mimes:jpeg,png,jpg|max:2048' : 'nullable|string|max:500',
            'address'               => 'nullable|string',
            'latitude'              => 'nullable|numeric|between:-90,90',
            'longitude'             => 'nullable|numeric|between:-180,180',
            'receive_notifications' => 'sometimes|boolean',
        ];
    }
}
