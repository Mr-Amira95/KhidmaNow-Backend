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
            ->where('ratee_id', $user->id);

        if ($request->filled('rating_type')) {
            $query->where('rating_type', $request->rating_type);
        }

        return $this->paginated(RateResource::class, $query->latest());
    }

    public function show(Request $request, Rate $rate)
    {
        $user = $request->user();
        if ((int) $rate->rater_id !== (int) $user->id && (int) $rate->ratee_id !== (int) $user->id) {
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
            if (!$user->provider || (int) $serviceRequest->provider_id !== (int) $user->provider->id) {
                return $this->error('You are not part of this request.', 403);
            }
            $ratingType = 'customer';
            $rateeId = $serviceRequest->user_id;
        } else {
            if ((int) $serviceRequest->user_id !== (int) $user->id) {
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

        // Recalculate average rating and ratings count for the ratee
        $ratee = \App\Models\User::find($rateeId);
        if ($ratee) {
            $stats = Rate::where('ratee_id', $rateeId)
                ->selectRaw('COUNT(*) as count, AVG(rate) as average')
                ->first();

            $ratee->update([
                'average_rating' => round($stats->average ?? 0.0, 1),
                'ratings_count'  => $stats->count ?? 0,
            ]);
        }

        $rate->load(['rater', 'ratee', 'serviceRequest']);

        return $this->success(new RateResource($rate), 'Feedback submitted successfully.', 201);
    }
}
