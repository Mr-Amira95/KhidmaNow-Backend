<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'key'        => $this->key,
            'value'      => $this->value,
            'type'       => $this->type,
            'country_id' => $this->country_id,
            'country'    => new CountryResource($this->whenLoaded('country')),
            'updated_at' => $this->updated_at,
        ];
    }
}
