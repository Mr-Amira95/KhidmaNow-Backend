<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->body,
            'icon'        => $this->icon,
            'timestamp'   => $this->created_at,
            'action'      => $this->type,
            'action_id'   => $this->type_id,
            'is_read'     => $this->is_read,
            'user'        => new UserResource($this->whenLoaded('user')),
        ];
    }
}
