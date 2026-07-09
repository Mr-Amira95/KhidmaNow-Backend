<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationBidResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'quotation_id'  => $this->quotation_id,
            'provider_id'   => $this->provider_id,
            'price'         => $this->price,
            'note'          => $this->note,
            'status'        => $this->status,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
            'provider'      => new ProviderResource($this->whenLoaded('provider')),
        ];
    }
}
