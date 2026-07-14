<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationBid;
use App\Models\QuotationTrack;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestTrack;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class QuotationService
{
    public function approveBid(Quotation $quotation, QuotationBid $bid, User $changedBy): ServiceRequest
    {
        if ($quotation->status !== 'open') {
            throw new InvalidArgumentException("This quotation is already '{$quotation->status}'.");
        }

        if ((int) $bid->quotation_id !== (int) $quotation->id) {
            throw new InvalidArgumentException('This bid does not belong to the given quotation.');
        }

        return DB::transaction(function () use ($quotation, $bid, $changedBy) {
            $bid->update(['status' => 'accepted']);

            $quotation->bids()
                ->where('id', '!=', $bid->id)
                ->update(['status' => 'rejected']);

            $fromStatus = $quotation->status;
            $quotation->update([
                'status'          => 'closed',
                'accepted_bid_id' => $bid->id,
            ]);

            QuotationTrack::create([
                'quotation_id' => $quotation->id,
                'from_status'  => $fromStatus,
                'to_status'    => 'closed',
                'changed_by'   => $changedBy->id,
                'date_time'    => now(),
            ]);

            $serviceRequest = ServiceRequest::create([
                'user_id'         => $quotation->user_id,
                'provider_id'     => $bid->provider_id,
                'quotation_id'    => $quotation->id,
                'source'          => 'quotation',
                'title'           => $quotation->title,
                'description'     => $quotation->description,
                'price'           => $bid->price,
                'status'          => 'approved',
                'payment_status'  => 'unpaid',
                'latitude'        => $quotation->latitude,
                'longitude'       => $quotation->longitude,
                'address'         => $quotation->address,
                'scheduled_at'    => $quotation->scheduled_at,
            ]);

            ServiceRequestTrack::create([
                'service_request_id' => $serviceRequest->id,
                'from_status'        => null,
                'to_status'          => 'approved',
                'changed_by'         => $changedBy->id,
                'date_time'          => now(),
            ]);

            $bid->loadMissing('provider');
            if ($bid->provider) {
                \App\Services\NotificationService::send(
                    $bid->provider->user_id,
                    'Bid Approved',
                    'Your bid of ' . $bid->price . ' for "' . $quotation->title . '" has been approved.',
                    'service_request',
                    $serviceRequest->id
                );
            }

            return $serviceRequest;
        });
    }
}
