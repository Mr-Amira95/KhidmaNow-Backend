<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'quotation_id' => $this->quotation_id,
            'url'          => $this->url,
            'type'         => $this->type,
            'created_at'   => $this->created_at,
        ];
    }
}
