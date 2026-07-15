<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatbotMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chatbot_room_id' => $this->chatbot_room_id,
            'role' => $this->role,
            'direction' => $this->direction,
            'message' => $this->message,
            'quotation' => new QuotationResource($this->whenLoaded('quotation')),
            'suggestions' => ChatbotMessageSuggestionResource::collection($this->whenLoaded('suggestions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
