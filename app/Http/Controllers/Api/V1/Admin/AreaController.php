<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAreaRequest;
use App\Http\Requests\Admin\UpdateAreaRequest;
use App\Http\Resources\AreaResource;
use App\Http\Traits\ApiResponse;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Area::with('city');

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name_ar', 'like', "%{$request->search}%")
                  ->orWhere('name_en', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated(AreaResource::class, $query->orderBy('name_en'));
    }

    public function store(StoreAreaRequest $request)
    {
        $area = Area::create($request->validated())->refresh();
        $area->load('city');
        return $this->success(new AreaResource($area), 'Area created successfully.', 201);
    }

    public function show(Area $area)
    {
        $area->load('city');
        return $this->success(new AreaResource($area));
    }

    public function update(UpdateAreaRequest $request, Area $area)
    {
        $area->update($request->validated());
        $area->load('city');
        return $this->success(new AreaResource($area), 'Area updated successfully.');
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return $this->success([], 'Area deleted successfully.');
    }
}
