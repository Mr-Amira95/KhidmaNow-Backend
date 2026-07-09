<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name_ar'        => 'required|string|max:255',
            'name_en'        => 'required|string|max:255',
            'iso'            => 'required|string|size:3|unique:countries,iso',
            'phone_code'     => 'required|string|max:10',
            'currency_code'  => 'required|string|size:3',
            'currency_value' => 'required|numeric|min:0',
            'flag'           => $this->hasFile('flag') ? 'image|mimes:jpeg,png,jpg,svg|max:2048' : 'nullable|string|max:500',
            'is_active'      => 'nullable|boolean',
        ];
    }
}
