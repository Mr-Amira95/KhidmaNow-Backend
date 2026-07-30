<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DebtPaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'provider_id'      => $this->provider_id,
            'amount'           => $this->amount,
            'payment_method'   => $this->payment_method,
            'status'           => $this->status,
            'transaction_ref'  => $this->transaction_ref,
            'receipt_url'      => $this->receipt_path ? Storage::disk('public')->url($this->receipt_path) : null,
            'rejection_reason' => $this->rejection_reason,
            'checkout_url'     => $this->when($this->payment_method === 'card' && $this->status === 'unpaid' && $this->stripe_checkout_url, $this->stripe_checkout_url),
            'paid_at'          => $this->paid_at,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'provider'         => new ProviderResource($this->whenLoaded('provider')),
        ];
    }
}
