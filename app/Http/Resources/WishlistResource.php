<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'item_type'  => $this->item_type,
            'item_id'    => $this->item_id,
            'item'       => $this->resolveItem(),
            'created_at' => $this->created_at,
        ];
    }

    private function resolveItem(): mixed
    {
        $item = $this->item ?? null;
        if (!$item) {
            return null;
        }

        return match ($this->item_type) {
            'category'     => new CategoryResource($item),
            'sub_category' => new SubCategoryResource($item),
            'provider'     => new ProviderResource($item),
            default        => null,
        };
    }
}
