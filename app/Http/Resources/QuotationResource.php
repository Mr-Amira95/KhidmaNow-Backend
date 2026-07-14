<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'category_id'      => $this->category_id,
            'sub_category_id'  => $this->sub_category_id,
            'title'            => $this->title,
            'description'      => $this->description,
            'price'            => $this->price,
            'note'             => $this->note,
            'latitude'         => $this->latitude,
            'longitude'        => $this->longitude,
            'address'          => $this->address,
            'scheduled_at'     => $this->scheduled_at,
            'status'           => $this->status,
            'accepted_bid_id'  => $this->accepted_bid_id,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'user'             => new UserResource($this->whenLoaded('user')),
            'category'         => new CategoryResource($this->whenLoaded('category')),
            'sub_category'     => new SubCategoryResource($this->whenLoaded('subCategory')),
            'bids'             => QuotationBidResource::collection($this->whenLoaded('bids')),
            'track'            => QuotationTrackResource::collection($this->whenLoaded('track')),
            'service_request'  => new ServiceRequestResource($this->whenLoaded('serviceRequest')),
        ];
    }
}
