<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'phone'          => $this->phone,
            'email'          => $this->email,
            'status'         => $this->status,
            'is_super_admin' => $this->is_super_admin,
            'roles'          => RoleResource::collection($this->whenLoaded('roles')),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
