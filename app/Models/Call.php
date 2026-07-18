<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'chat_id',
        'initiated_by',
        'call_type',
        'agora_channel',
        'status',
        'started_at',
        'accepted_at',
        'ended_at',
        'duration_seconds',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'accepted_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_id');
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
