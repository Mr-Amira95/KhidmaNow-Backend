<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequestRequest;
use App\Http\Requests\Admin\UpdateServiceRequestStatusRequest;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Traits\ApiResponse;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestTrack;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = ServiceRequest::with(['user', 'provider.user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        return $this->paginated(ServiceRequestResource::class, $query->latest());
    }

    public function show(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['user', 'provider.user', 'attachments', 'payment', 'track.changedBy', 'rates']);
        return $this->success(new ServiceRequestResource($serviceRequest));
    }

    public function store(StoreServiceRequestRequest $request)
    {
        $serviceRequest = ServiceRequest::create([
            ...$request->validated(),
            'status'         => $request->status ?? 'pending',
            'payment_status' => $request->payment_status ?? 'unpaid',
            'source'         => 'direct',
        ]);

        ServiceRequestTrack::create([
            'service_request_id' => $serviceRequest->id,
            'from_status'        => null,
            'to_status'          => $serviceRequest->status,
            'changed_by'         => $request->user()->id,
            'date_time'          => now(),
        ]);

        return $this->success(new ServiceRequestResource($serviceRequest), 'Service request created successfully.', 201);
    }

    public function updateStatus(UpdateServiceRequestStatusRequest $request, ServiceRequest $serviceRequest)
    {
        $fromStatus = $serviceRequest->status;
        $serviceRequest->update(['status' => $request->status]);

        ServiceRequestTrack::create([
            'service_request_id' => $serviceRequest->id,
            'from_status'        => $fromStatus,
            'to_status'          => $request->status,
            'changed_by'         => $request->user()->id,
            'date_time'          => now(),
        ]);

        return $this->success(new ServiceRequestResource($serviceRequest), 'Status updated successfully.');
    }
}
