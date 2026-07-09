<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'phone'          => $this->phone,
            'email'          => $this->email,
            'profile_image'  => $this->profile_image ? (str_starts_with($this->profile_image, 'http') ? $this->profile_image : Storage::disk('public')->url($this->profile_image)) : null,
            'user_type'      => $this->user_type,
            'average_rating' => $this->average_rating,
            'ratings_count'  => $this->ratings_count,
            'status'         => $this->status,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'address'        => $this->address,
            'last_login_at'  => $this->last_login_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
            'provider'       => new ProviderResource($this->whenLoaded('provider')),
        ];
    }
}
