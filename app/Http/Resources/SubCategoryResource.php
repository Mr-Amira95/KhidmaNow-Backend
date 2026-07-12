<?php

namespace App\Http\Resources;

use App\Services\WishlistStatusResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SubCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'category_id'    => $this->category_id,
            'name_ar'        => $this->name_ar,
            'name_en'        => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'icon'           => $this->icon ? (str_starts_with($this->icon, 'http') ? $this->icon : Storage::disk('public')->url($this->icon)) : null,
            'is_active'      => $this->is_active,
            'is_wishlist'    => app(WishlistStatusResolver::class)
                ->isWishlisted($request->user('sanctum')?->id, 'sub_category', $this->id),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'category'       => new CategoryResource($this->whenLoaded('category')),
            'providers'      => ProviderResource::collection($this->whenLoaded('providers')),
        ];
    }
}
