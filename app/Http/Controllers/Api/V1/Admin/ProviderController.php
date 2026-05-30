<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
use App\Http\Traits\ApiResponse;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Provider::with(['user', 'city']);

        if ($request->filled('is_verified')) {
            $query->where('is_verified', filter_var($request->is_verified, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('availability_status')) {
            $query->where('availability_status', $request->availability_status);
        }
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('business_name', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%"));
            });
        }

        return $this->paginated(ProviderResource::class, $query->latest());
    }

    public function show(Provider $provider)
    {
        $provider->load(['user', 'city', 'documents', 'subCategories.subCategory']);
        return $this->success(new ProviderResource($provider));
    }

    public function verify(Provider $provider)
    {
        $provider->update(['is_verified' => true]);
        return $this->success(new ProviderResource($provider), 'Provider verified successfully.');
    }

    public function unverify(Provider $provider)
    {
        $provider->update(['is_verified' => false]);
        return $this->success(new ProviderResource($provider), 'Provider unverified.');
    }

    public function destroy(Provider $provider)
    {
        $provider->delete();
        return $this->success([], 'Provider deleted successfully.');
    }
}
