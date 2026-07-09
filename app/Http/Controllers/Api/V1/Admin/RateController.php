<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRateRequest;
use App\Http\Resources\RateResource;
use App\Http\Traits\ApiResponse;
use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Rate::with(['rater', 'ratee', 'serviceRequest']);

        if ($request->filled('rating_type')) {
            $query->where('rating_type', $request->rating_type);
        }
        if ($request->filled('ratee_id')) {
            $query->where('ratee_id', $request->ratee_id);
        }

        return $this->paginated(RateResource::class, $query->latest());
    }

    public function show(Rate $rate)
    {
        $rate->load(['rater', 'ratee', 'serviceRequest']);
        return $this->success(new RateResource($rate));
    }

    public function store(StoreRateRequest $request)
    {
        $rate = Rate::create($request->validated());
        $rate->load(['rater', 'ratee', 'serviceRequest']);

        return $this->success(new RateResource($rate), 'Feedback created successfully.', 201);
    }

    public function destroy(Rate $rate)
    {
        $rate->delete();
        return $this->success([], 'Review deleted successfully.');
    }
}
