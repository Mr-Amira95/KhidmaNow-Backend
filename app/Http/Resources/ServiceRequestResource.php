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
            'sub_category_id'=> $this->sub_category_id,
            'title'          => $this->title,
            'description'    => $this->description,
            'price'          => $this->price,
            'status'         => $this->status,
            'payment_status' => $this->payment_status,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'address'        => $this->address,
            'note'           => $this->note,
            'scheduled_at'   => $this->scheduled_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'user'           => new UserResource($this->whenLoaded('user')),
            'provider'       => new ProviderResource($this->whenLoaded('provider')),
            'sub_category'   => new SubCategoryResource($this->whenLoaded('subCategory')),
            'attachments'    => ServiceRequestAttachmentResource::collection($this->whenLoaded('attachments')),
            'payment'        => new PaymentResource($this->whenLoaded('payment')),
        ];
    }
}
