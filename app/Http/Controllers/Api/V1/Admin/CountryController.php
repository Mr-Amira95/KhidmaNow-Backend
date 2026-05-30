<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCountryRequest;
use App\Http\Requests\Admin\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Http\Traits\ApiResponse;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Country::query();

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('iso', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated(CountryResource::class, $query->orderBy('name'));
    }

    public function store(StoreCountryRequest $request)
    {
        $country = Country::create($request->validated());
        return $this->success(new CountryResource($country), 'Country created successfully.', 201);
    }

    public function show(Country $country)
    {
        return $this->success(new CountryResource($country));
    }

    public function update(UpdateCountryRequest $request, Country $country)
    {
        $country->update($request->validated());
        return $this->success(new CountryResource($country), 'Country updated successfully.');
    }

    public function destroy(Country $country)
    {
        $country->delete();
        return $this->success([], 'Country deleted successfully.');
    }
}
