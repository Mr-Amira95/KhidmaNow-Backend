<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotRoom extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'direction',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatbotMessage::class)->latestOfMany();
    }
}
