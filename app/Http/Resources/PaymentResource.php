<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'user_id'            => $this->user_id,
            'service_request_id' => $this->service_request_id,
            'amount'             => $this->amount,
            'payment_method'     => $this->payment_method,
            'status'             => $this->status,
            'transaction_ref'    => $this->transaction_ref,
            'paid_at'            => $this->paid_at,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            'user'               => new UserResource($this->whenLoaded('user')),
            'service_request'    => new ServiceRequestResource($this->whenLoaded('serviceRequest')),
        ];
    }
}
