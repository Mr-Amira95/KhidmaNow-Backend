<?php

namespace App\Services;

use App\Models\Wishlist;

class WishlistStatusResolver
{
    protected array $cache = [];

    public function isWishlisted(?int $userId, string $itemType, int $itemId): bool
    {
        if (!$userId) {
            return false;
        }

        if (!isset($this->cache[$userId][$itemType])) {
            $this->cache[$userId][$itemType] = Wishlist::where('user_id', $userId)
                ->where('item_type', $itemType)
                ->pluck('item_id')
                ->flip()
                ->all();
        }

        return isset($this->cache[$userId][$itemType][$itemId]);
    }
}
