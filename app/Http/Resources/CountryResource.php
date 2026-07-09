<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'flag'           => $this->flag ? (str_starts_with($this->flag, 'http') ? $this->flag : Storage::disk('public')->url($this->flag)) : null,
            'name_ar'        => $this->name_ar,
            'name_en'        => $this->name_en,
            'iso'            => $this->iso,
            'phone_code'     => $this->phone_code,
            'currency_code'  => $this->currency_code,
            'currency_value' => $this->currency_value,
            'is_active'      => $this->is_active,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
