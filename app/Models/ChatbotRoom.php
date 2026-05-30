<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotRoom extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatbotMessage::class);
    }
}
