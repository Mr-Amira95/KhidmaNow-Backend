<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $countryId = $this->route('country')?->id ?? $this->route('country');
        return [
            'name_ar'        => 'sometimes|string|max:255',
            'name_en'        => 'sometimes|string|max:255',
            'iso'            => "sometimes|string|size:3|unique:countries,iso,{$countryId}",
            'phone_code'     => 'sometimes|string|max:10',
            'currency_code'  => 'sometimes|string|size:3',
            'currency_value' => 'sometimes|numeric|min:0',
            'flag'           => $this->hasFile('flag') ? 'image|mimes:jpeg,png,jpg,svg|max:2048' : 'nullable|string|max:500',
            'is_active'      => 'nullable|boolean',
        ];
    }
}
