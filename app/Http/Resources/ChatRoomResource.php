<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'unread_count' => (int) ($this->unread_count ?? 0),
            'client' => new UserResource($this->whenLoaded('user')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'last_message' => new MessageResource($this->whenLoaded('latestMessage')),
        ];
    }
}
