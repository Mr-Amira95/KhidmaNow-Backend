<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class IntroScreenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'image'           => $this->image ? (str_starts_with($this->image, 'http') ? $this->image : Storage::disk('public')->url($this->image)) : null,
            'title_ar'        => $this->title_ar,
            'title_en'        => $this->title_en,
            'description_ar'  => $this->description_ar,
            'description_en'  => $this->description_en,
            'order'           => $this->order,
            'is_active'       => $this->is_active,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
