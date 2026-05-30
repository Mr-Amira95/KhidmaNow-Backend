<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'service_request_id' => $this->service_request_id,
            'url'                => $this->url,
            'type'               => $this->type,
            'created_at'         => $this->created_at,
        ];
    }
}
