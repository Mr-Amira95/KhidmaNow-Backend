<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'provider_id'    => $this->provider_id,
            'quotation_id'   => $this->quotation_id,
            'chat_room_id'   => $this->chat_room_id,
            'source'         => $this->source,
            'title'          => $this->title,
            'description'    => $this->description,
            'price'          => $this->price,
            'status'         => $this->status,
            'payment_status' => $this->payment?->status ?? 'unpaid',
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'address'        => $this->address,
            'note'           => $this->note,
            'scheduled_at'   => $this->scheduled_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'payment_id'     => $this->payment?->id,
            'user'           => new UserResource($this->whenLoaded('user')),
            'provider'       => new ProviderResource($this->whenLoaded('provider')),
            'attachments'    => ServiceRequestAttachmentResource::collection($this->whenLoaded('attachments')),
            'payment'        => $this->payment ? new PaymentResource($this->payment) : null,
            'track'          => ServiceRequestTrackResource::collection($this->whenLoaded('track')),
            'rates'          => RateResource::collection($this->whenLoaded('rates')),
            'feedback'       => $this->whenLoaded('rates', function ($rates) use ($request) {
                $rate = $rates->firstWhere('rater_id', $request->user()?->id);

                return $rate ? [
                    'id'          => $rate->id,
                    'rate'        => $rate->rate,
                    'feedback'    => $rate->feedback,
                    'rating_type' => $rate->rating_type,
                    'created_at'  => $rate->created_at,
                ] : null;
            }, null),
        ];
    }
}
