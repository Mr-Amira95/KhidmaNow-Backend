<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProviderDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'provider_id'      => $this->provider_id,
            'type'             => $this->type,
            'document_url'     => $this->document_url ? (str_starts_with($this->document_url, 'http') ? $this->document_url : Storage::disk('public')->url($this->document_url)) : null,
            'status'           => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'provider'         => new ProviderResource($this->whenLoaded('provider')),
        ];
    }
}
