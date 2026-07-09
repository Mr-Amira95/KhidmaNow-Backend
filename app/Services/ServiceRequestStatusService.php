<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\ServiceRequestTrack;
use App\Models\User;
use InvalidArgumentException;

class ServiceRequestStatusService
{
    private const TRANSITIONS = [
        'pending'     => ['approved', 'rejected', 'cancelled'],
        'approved'    => ['in_progress'],
        'in_progress' => ['completed'],
        'completed'   => ['confirmed'],
    ];

    public function transition(ServiceRequest $serviceRequest, string $toStatus, User $changedBy): ServiceRequest
    {
        $fromStatus = $serviceRequest->status;
        $allowed = self::TRANSITIONS[$fromStatus] ?? [];

        if (!in_array($toStatus, $allowed, true)) {
            throw new InvalidArgumentException("Cannot move a request from '{$fromStatus}' to '{$toStatus}'.");
        }

        if ($toStatus === 'in_progress' && $serviceRequest->payment_status !== 'paid') {
            throw new InvalidArgumentException('The invoice must be paid before the request can start.');
        }

        $serviceRequest->update(['status' => $toStatus]);

        ServiceRequestTrack::create([
            'service_request_id' => $serviceRequest->id,
            'from_status'        => $fromStatus,
            'to_status'          => $toStatus,
            'changed_by'         => $changedBy->id,
            'date_time'          => now(),
        ]);

        return $serviceRequest;
    }
}
