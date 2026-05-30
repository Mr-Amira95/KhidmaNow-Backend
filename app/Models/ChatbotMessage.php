<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'chatbot_room_id',
        'role',
        'message',
    ];

    public function room()
    {
        return $this->belongsTo(ChatbotRoom::class, 'chatbot_room_id');
    }

    public function suggestions()
    {
        return $this->hasMany(ChatbotMessageSuggestion::class);
    }
}
