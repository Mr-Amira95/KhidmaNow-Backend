<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Traits\ApiResponse;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = City::where('is_active', true);

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->country_id);
        }

        return $this->success(CityResource::collection($query->orderBy('name_en')->get()));
    }
}
