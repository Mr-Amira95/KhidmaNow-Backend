<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'service_request_id' => $this->service_request_id,
            'rater_id'           => $this->rater_id,
            'ratee_id'           => $this->ratee_id,
            'rating_type'        => $this->rating_type,
            'rate'               => $this->rate,
            'feedback'           => $this->feedback,
            'created_at'         => $this->created_at,
            'rater'              => new UserResource($this->whenLoaded('rater')),
            'ratee'              => new UserResource($this->whenLoaded('ratee')),
            'service_request'    => new ServiceRequestResource($this->whenLoaded('serviceRequest')),
        ];
    }
}
