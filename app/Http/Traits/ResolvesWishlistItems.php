<?php

namespace App\Http\Traits;

use App\Models\Wishlist;
use Illuminate\Support\Collection;

trait ResolvesWishlistItems
{
    protected function hydrateWishlistItems(iterable $wishlists): void
    {
        Collection::make($wishlists)
            ->groupBy('item_type')
            ->each(function (Collection $items, string $type) {
                $modelClass = Wishlist::TYPE_MODELS[$type] ?? null;
                if (!$modelClass) {
                    return;
                }

                $records = $modelClass::whereIn('id', $items->pluck('item_id')->unique())->get()->keyBy('id');
                $items->each(fn ($wishlist) => $wishlist->setAttribute('item', $records->get($wishlist->item_id)));
            });
    }
}
