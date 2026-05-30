<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'provider_id'        => $this->provider_id,
            'service_request_id' => $this->service_request_id,
            'amount'             => $this->amount,
            'commission'         => $this->commission,
            'status'             => $this->status,
            'paid_at'            => $this->paid_at,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            'provider'           => new ProviderResource($this->whenLoaded('provider')),
            'service_request'    => new ServiceRequestResource($this->whenLoaded('serviceRequest')),
        ];
    }
}
