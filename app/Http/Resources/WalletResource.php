<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'balance'      => $this->balance,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'user'         => new UserResource($this->whenLoaded('user')),
            'transactions' => WalletTransactionResource::collection($this->whenLoaded('transactions')),
        ];
    }
}
