<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'last_message_at',
        'deleted_by_user_at',
        'deleted_by_provider_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'deleted_by_user_at' => 'datetime',
            'deleted_by_provider_at' => 'datetime',
        ];
    }

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
        return $this->hasMany(Message::class, 'chat_id');
    }

    public function calls()
    {
        return $this->hasMany(Call::class, 'chat_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'chat_id')->latestOfMany();
    }

    /**
     * Is the given user a participant (client or provider side) of this chat room?
     */
    public function hasParticipant(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $user->user_type === 'provider' && $user->provider && $this->provider_id === $user->provider->id;
    }

    /**
     * Which soft-delete column applies to the given user's side of this chat.
     */
    public function deletedAtColumnFor(User $user): string
    {
        return $user->user_type === 'provider' ? 'deleted_by_provider_at' : 'deleted_by_user_at';
    }
}
