<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRateRequest;
use App\Http\Resources\RateResource;
use App\Http\Traits\ApiResponse;
use App\Models\Rate;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class RateController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Rate::with(['rater', 'ratee', 'serviceRequest'])
            ->where(function ($q) use ($user) {
                $q->where('rater_id', $user->id)->orWhere('ratee_id', $user->id);
            });

        if ($request->filled('rating_type')) {
            $query->where('rating_type', $request->rating_type);
        }

        return $this->paginated(RateResource::class, $query->latest());
    }

    public function show(Request $request, Rate $rate)
    {
        $user = $request->user();
        if ($rate->rater_id !== $user->id && $rate->ratee_id !== $user->id) {
            return $this->error('You are not part of this feedback.', 403);
        }

        $rate->load(['rater', 'ratee', 'serviceRequest']);
        return $this->success(new RateResource($rate));
    }

    public function store(StoreRateRequest $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();

        if (!in_array($serviceRequest->status, ['completed', 'confirmed'], true)) {
            return $this->error('Feedback can only be left once the request is completed.', 422);
        }

        if ($user->user_type === 'provider') {
            if (!$user->provider || $serviceRequest->provider_id !== $user->provider->id) {
                return $this->error('You are not part of this request.', 403);
            }
            $ratingType = 'customer';
            $rateeId = $serviceRequest->user_id;
        } else {
            if ($serviceRequest->user_id !== $user->id) {
                return $this->error('You are not part of this request.', 403);
            }
            $serviceRequest->loadMissing('provider');
            $ratingType = 'provider';
            $rateeId = $serviceRequest->provider->user_id;
        }

        $alreadyRated = Rate::where('service_request_id', $serviceRequest->id)
            ->where('rater_id', $user->id)
            ->exists();

        if ($alreadyRated) {
            return $this->error('You already left feedback for this request.', 422);
        }

        $rate = Rate::create([
            'service_request_id' => $serviceRequest->id,
            'rater_id'           => $user->id,
            'ratee_id'           => $rateeId,
            'rating_type'        => $ratingType,
            'rate'               => $request->rate,
            'feedback'           => $request->feedback,
        ]);

        $rate->load(['rater', 'ratee', 'serviceRequest']);

        return $this->success(new RateResource($rate), 'Feedback submitted successfully.', 201);
    }
}
