<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyCliqDetailRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'alias'       => 'required|string|max:255',
            'bank_name'   => 'required|string|max:255',
            'holder_name' => 'required|string|max:255',
        ];
    }
}
