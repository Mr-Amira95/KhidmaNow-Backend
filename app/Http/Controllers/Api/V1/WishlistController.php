<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\ResolvesWishlistItems;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponse, ResolvesWishlistItems;

    public function index(Request $request)
    {
        $query = Wishlist::where('user_id', $request->user()->id);

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        return $this->paginated(
            WishlistResource::class,
            $query->latest(),
            15,
            fn ($items) => $this->hydrateWishlistItems($items)
        );
    }

    public function store(StoreWishlistRequest $request)
    {
        $wishlist = Wishlist::firstOrCreate([
            'user_id'   => $request->user()->id,
            'item_type' => $request->validated('item_type'),
            'item_id'   => $request->validated('item_id'),
        ]);

        $this->hydrateWishlistItems([$wishlist]);

        return $this->success(new WishlistResource($wishlist), 'Added to wishlist.', 201);
    }

    public function destroy(Request $request, string $itemType, int $itemId)
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)
            ->where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->first();

        if (!$wishlist) {
            return $this->error('Item not found in wishlist.', 404);
        }

        $wishlist->delete();

        return $this->success([], 'Removed from wishlist.');
    }
}
