<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivacyPolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'content_ar' => $this->content_ar,
            'content_en' => $this->content_en,
            'updated_at' => $this->updated_at,
        ];
    }
}
