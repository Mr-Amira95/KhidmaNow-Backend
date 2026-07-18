<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'initiated_by' => $this->initiated_by,
            'call_type' => $this->call_type,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'accepted_at' => $this->accepted_at,
            'ended_at' => $this->ended_at,
            'duration_seconds' => $this->duration_seconds,
            'agora' => [
                'app_id' => config('services.agora.app_id'),
                'channel' => $this->agora_channel,
                'token' => $this->token ?? null,
                'uid' => $this->uid ?? null,
                'expire_at' => $this->expire_at ?? null,
            ],
        ];
    }
}
