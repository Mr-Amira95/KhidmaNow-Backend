<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Http\Traits\ApiResponse;
use App\Models\Provider;
use App\Models\ProviderSubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    use ApiResponse;

    public function available(Request $request)
    {
        $provider = $request->user()->provider;
        $provider->update(['availability_status' => 'online']);

        return $this->success(new ProviderResource($provider), 'You are now available.');
    }

    public function unavailable(Request $request)
    {
        $provider = $request->user()->provider;
        $provider->update(['availability_status' => 'offline']);

        return $this->success(new ProviderResource($provider), 'You are now unavailable.');
    }

    /**
     * Get the authenticated provider's own details.
     */
    public function profile(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            return $this->error('Provider profile not found.', 404);
        }

        $provider->load(['user', 'city', 'documents', 'subCategories.subCategory']);

        return $this->success(new ProviderResource($provider));
    }

    /**
     * Update the authenticated provider's own details.
     */
    public function updateProfile(UpdateProviderRequest $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            return $this->error('Provider profile not found.', 404);
        }

        $data = $request->validated();
        $subCategoryIds = $data['sub_category_ids'] ?? null;
        unset($data['sub_category_ids']);

        DB::transaction(function () use ($provider, $data, $subCategoryIds) {
            $provider->update($data);

            if ($subCategoryIds !== null) {
                $provider->subCategories()->delete();
                foreach ($subCategoryIds as $subCategoryId) {
                    ProviderSubCategory::create([
                        'provider_id' => $provider->id,
                        'sub_category_id' => $subCategoryId,
                    ]);
                }
            }
        });

        $provider->load(['user', 'city', 'documents', 'subCategories.subCategory']);

        return $this->success(new ProviderResource($provider), 'Provider profile updated successfully.');
    }

    /**
     * Public: get a service provider's details by id.
     */
    public function show(Provider $provider)
    {
        $provider->load(['user', 'city', 'subCategories.subCategory']);

        return $this->success(new ProviderResource($provider));
    }

    /**
     * Public: search & filter service providers.
     */
    public function index(Request $request)
    {
        $request->validate([
            'category_id'         => 'nullable|integer|exists:categories,id',
            'sub_category_id'     => 'nullable|integer|exists:sub_categories,id',
            'city_id'             => 'nullable|integer|exists:cities,id',
            'availability_status' => 'nullable|in:online,offline,busy',
            'min_rating'          => 'nullable|numeric|min:0|max:5',
            'search'              => 'nullable|string|max:255',
            'sort'                => 'nullable|in:rating,newest,experience',
        ]);

        $query = Provider::query()->with(['user', 'city', 'subCategories.subCategory.category']);

        if ($request->filled('sub_category_id')) {
            $query->whereHas('subCategories', fn($q) => $q->where('sub_category_id', $request->sub_category_id));
        }

        if ($request->filled('category_id')) {
            $query->whereHas('subCategories.subCategory', fn($q) => $q->where('category_id', $request->category_id));
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('availability_status')) {
            $query->where('availability_status', $request->availability_status);
        }

        if ($request->filled('min_rating')) {
            $query->whereHas('user', fn($q) => $q->where('average_rating', '>=', $request->min_rating));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('business_name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%")
                      ->orWhere('phone', 'like', "%{$request->search}%"));
            });
        }

        match ($request->input('sort', 'rating')) {
            'newest'     => $query->latest(),
            'experience' => $query->orderByDesc('experience_years'),
            default      => $query->orderByDesc(
                User::select('average_rating')->whereColumn('users.id', 'providers.user_id')
            ),
        };

        return $this->paginated(ProviderResource::class, $query);
    }
}
