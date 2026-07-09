<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Http\Traits\ApiResponse;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Area::where('is_active', true);

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        return $this->success(AreaResource::collection($query->orderBy('name_en')->get()));
    }
}
