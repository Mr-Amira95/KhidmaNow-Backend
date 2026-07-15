<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'chatbot_room_id',
        'role',
        'direction',
        'message',
        'quotation_id',
    ];

    public function room()
    {
        return $this->belongsTo(ChatbotRoom::class, 'chatbot_room_id');
    }

    public function suggestions()
    {
        return $this->hasMany(ChatbotMessageSuggestion::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
