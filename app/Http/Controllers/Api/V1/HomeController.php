<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Traits\ApiResponse;
use App\Models\Category;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ApiResponse;

    protected const ACTIVE_SERVICE_REQUEST_STATUSES = ['pending', 'approved', 'in_progress'];

    /**
     * Client home screen: public data for guests, plus personalized data for logged-in customers.
     */
    public function index(Request $request)
    {
        $user = $request->user('sanctum');

        $categories = Category::where('is_active', true)->latest()->take(12)->get();

        $featuredProviders = Provider::query()
            ->with(['user', 'city', 'subCategories.subCategory'])
            ->where('is_verified', true)
            ->orderByDesc(
                User::select('average_rating')->whereColumn('users.id', 'providers.user_id')
            )
            ->take(10)
            ->get();

        $data = [
            'categories'         => CategoryResource::collection($categories),
            'featured_providers' => ProviderResource::collection($featuredProviders),
        ];

        if ($user && $user->user_type === 'customer') {
            $activeStatuses = self::ACTIVE_SERVICE_REQUEST_STATUSES;

            $data['wallet_balance'] = $user->wallet?->balance ?? 0;
            $data['wishlist_count'] = $user->wishlists()->count();
            $data['unread_notifications_count'] = $user->notifications()->where('is_read', false)->count();
            $data['active_service_requests_count'] = $user->serviceRequests()
                ->whereIn('status', $activeStatuses)
                ->count();
            $data['active_service_requests'] = ServiceRequestResource::collection(
                $user->serviceRequests()
                    ->whereIn('status', $activeStatuses)
                    ->with(['provider.user'])
                    ->latest()
                    ->take(5)
                    ->get()
            );
        }

        return $this->success($data);
    }
}
