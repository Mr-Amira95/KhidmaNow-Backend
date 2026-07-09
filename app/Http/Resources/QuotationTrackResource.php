<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationTrackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'quotation_id' => $this->quotation_id,
            'from_status'  => $this->from_status,
            'to_status'    => $this->to_status,
            'changed_by'   => $this->changed_by,
            'date_time'    => $this->date_time,
            'changed_by_user' => new UserResource($this->whenLoaded('changedBy')),
        ];
    }
}
