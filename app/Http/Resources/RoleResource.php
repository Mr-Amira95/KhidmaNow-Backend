<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'permissions_count' => $this->role_permissions_count ?? $this->whenLoaded('permissions', fn () => $this->permissions->count()),
            'permissions'       => PermissionResource::collection($this->whenLoaded('permissions')),
        ];
    }
}
