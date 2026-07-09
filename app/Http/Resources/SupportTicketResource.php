<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SupportTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'attachment_type' => $this->attachment_type,
            'attachment_url' => $this->attachment_url ? (str_starts_with($this->attachment_url, 'http') ? $this->attachment_url : Storage::disk('public')->url($this->attachment_url)) : null,
            'replies_count' => (int) ($this->replies_count ?? 0),
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'closed_by' => new UserResource($this->whenLoaded('closedBy')),
            'latest_reply' => new SupportTicketReplyResource($this->whenLoaded('latestReply')),
        ];
    }
}
