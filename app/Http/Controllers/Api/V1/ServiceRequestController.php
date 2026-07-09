<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequestRequest;
use App\Http\Requests\UpdateServiceRequestStatusRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Traits\ApiResponse;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestTrack;
use App\Services\ServiceRequestStatusService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ServiceRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = $request->user();

        $query = ServiceRequest::with(['user', 'provider.user', 'subCategory'])
            ->when($user->user_type === 'provider', fn ($q) => $q->where('provider_id', $user->provider->id))
            ->when($user->user_type !== 'provider', fn ($q) => $q->where('user_id', $user->id));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        return $this->paginated(ServiceRequestResource::class, $query->latest());
    }

    public function show(Request $request, ServiceRequest $serviceRequest)
    {
        $user = $request->user();
        if (!$this->isParticipant($user, $serviceRequest)) {
            return $this->error('You are not part of this request.', 403);
        }

        $serviceRequest->load(['user', 'provider.user', 'subCategory', 'attachments', 'payment', 'track.changedBy', 'rates']);
        return $this->success(new ServiceRequestResource($serviceRequest));
    }

    public function store(StoreServiceRequestRequest $request)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer') {
            return $this->error('Only clients can create a request this way.', 403);
        }

        $serviceRequest = ServiceRequest::create([
            ...$request->validated(),
            'user_id'        => $user->id,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'source'         => 'direct',
        ]);

        ServiceRequestTrack::create([
            'service_request_id' => $serviceRequest->id,
            'from_status'        => null,
            'to_status'          => 'pending',
            'changed_by'         => $user->id,
            'date_time'          => now(),
        ]);

        return $this->success(new ServiceRequestResource($serviceRequest), 'Service request created successfully.', 201);
    }

    public function updateStatus(UpdateServiceRequestStatusRequest $request, ServiceRequest $serviceRequest, ServiceRequestStatusService $statusService)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer' || $serviceRequest->user_id !== $user->id) {
            return $this->error('You are not allowed to change this request.', 403);
        }

        try {
            $statusService->transition($serviceRequest, $request->status, $user);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(new ServiceRequestResource($serviceRequest), 'Status updated successfully.');
    }

    private function isParticipant($user, ServiceRequest $serviceRequest): bool
    {
        if ($user->user_type === 'provider') {
            return $user->provider && $serviceRequest->provider_id === $user->provider->id;
        }

        return $serviceRequest->user_id === $user->id;
    }
}
