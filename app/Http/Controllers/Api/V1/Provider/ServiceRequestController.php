<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreServiceRequestRequest;
use App\Http\Requests\Provider\UpdateServiceRequestStatusRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Traits\ApiResponse;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestTrack;
use App\Services\ServiceRequestStatusService;
use InvalidArgumentException;

class ServiceRequestController extends Controller
{
    use ApiResponse;

    public function store(StoreServiceRequestRequest $request)
    {
        $provider = $request->user()->provider;

        $serviceRequest = ServiceRequest::create([
            ...$request->validated(),
            'provider_id'    => $provider->id,
            'status'         => 'approved',
            'payment_status' => 'unpaid',
            'source'         => 'chat',
        ]);

        ServiceRequestTrack::create([
            'service_request_id' => $serviceRequest->id,
            'from_status'        => null,
            'to_status'          => 'approved',
            'changed_by'         => $request->user()->id,
            'date_time'          => now(),
        ]);

        return $this->success(new ServiceRequestResource($serviceRequest), 'Service request created successfully.', 201);
    }

    public function updateStatus(UpdateServiceRequestStatusRequest $request, ServiceRequest $serviceRequest, ServiceRequestStatusService $statusService)
    {
        $provider = $request->user()->provider;
        if (!$provider || $serviceRequest->provider_id !== $provider->id) {
            return $this->error('You are not allowed to change this request.', 403);
        }

        if ($request->status === 'approved' && $request->filled('price')) {
            $serviceRequest->update(['price' => $request->price]);
        }

        try {
            $statusService->transition($serviceRequest, $request->status, $request->user());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(new ServiceRequestResource($serviceRequest), 'Status updated successfully.');
    }
}
