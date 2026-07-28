<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCliqDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'alias'       => $this->alias,
            'bank_name'   => $this->bank_name,
            'holder_name' => $this->holder_name,
            'updated_at'  => $this->updated_at,
        ];
    }
}
