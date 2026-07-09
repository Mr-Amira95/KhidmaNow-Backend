<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SupportTicketReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'message' => $this->message,
            'attachment_type' => $this->attachment_type,
            'attachment_url' => $this->attachment_url ? (str_starts_with($this->attachment_url, 'http') ? $this->attachment_url : Storage::disk('public')->url($this->attachment_url)) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'sender' => new UserResource($this->whenLoaded('sender')),
        ];
    }
}
