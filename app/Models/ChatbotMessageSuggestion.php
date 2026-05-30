<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessageSuggestion extends Model
{
    protected $fillable = [
        'chatbot_message_id',
        'provider_id',
    ];

    public function message()
    {
        return $this->belongsTo(ChatbotMessage::class, 'chatbot_message_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
