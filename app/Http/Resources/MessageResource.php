<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'sender_id' => $this->sender_id,
            'message' => $this->message,
            'media_type' => $this->media_type,
            'media_url' => $this->media_url ? (str_starts_with($this->media_url, 'http') ? $this->media_url : Storage::disk('public')->url($this->media_url)) : null,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sender' => new UserResource($this->whenLoaded('sender')),
            'call' => $this->when($this->media_type === 'call', function () {
                return [
                    'id' => $this->call_id,
                    'call_type' => $this->call?->call_type,
                    'status' => $this->call?->status,
                    'duration_seconds' => $this->call?->duration_seconds,
                ];
            }),
        ];
    }
}
