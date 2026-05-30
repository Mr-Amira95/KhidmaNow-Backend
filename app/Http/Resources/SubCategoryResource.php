<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'category_id' => $this->category_id,
            'name'        => $this->name,
            'description' => $this->description,
            'icon'        => $this->icon,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'category'    => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
