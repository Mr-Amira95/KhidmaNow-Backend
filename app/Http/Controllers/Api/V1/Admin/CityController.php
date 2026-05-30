<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Http\Requests\Admin\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Http\Traits\ApiResponse;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = City::with('country');

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return $this->paginated(CityResource::class, $query->orderBy('name'));
    }

    public function store(StoreCityRequest $request)
    {
        $city = City::create($request->validated());
        $city->load('country');
        return $this->success(new CityResource($city), 'City created successfully.', 201);
    }

    public function show(City $city)
    {
        $city->load('country');
        return $this->success(new CityResource($city));
    }

    public function update(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());
        $city->load('country');
        return $this->success(new CityResource($city), 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        $city->delete();
        return $this->success([], 'City deleted successfully.');
    }
}
