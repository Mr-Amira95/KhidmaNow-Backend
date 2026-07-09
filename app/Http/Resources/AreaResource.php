<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'city_id'    => $this->city_id,
            'name_ar'    => $this->name_ar,
            'name_en'    => $this->name_en,
            'is_active'  => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'city'       => new CityResource($this->whenLoaded('city')),
        ];
    }
}
