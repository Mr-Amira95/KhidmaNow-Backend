<?php

namespace App\Http\Resources;

use App\Services\WishlistStatusResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'city_id'             => $this->city_id,
            'business_name'       => $this->business_name,
            'description'         => $this->description,
            'experience_years'    => $this->experience_years,
            'availability_status' => $this->availability_status,
            'is_verified'         => $this->is_verified,
            'is_wishlist'         => app(WishlistStatusResolver::class)
                ->isWishlisted($request->user('sanctum')?->id, 'provider', $this->id),
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'user'                => new UserResource($this->whenLoaded('user')),
            'city'                => new CityResource($this->whenLoaded('city')),
            'documents'           => ProviderDocumentResource::collection($this->whenLoaded('documents')),
            'sub_categories'      => ProviderSubCategoryResource::collection($this->whenLoaded('subCategories')),
        ];
    }
}
