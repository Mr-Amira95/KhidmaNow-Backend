<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'provider_id'      => $this->provider_id,
            'type'             => $this->type,
            'document_url'     => $this->document_url,
            'status'           => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'provider'         => new ProviderResource($this->whenLoaded('provider')),
        ];
    }
}
