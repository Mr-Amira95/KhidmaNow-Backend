<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'type',
        'type_id',
        'response',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'response' => 'array',
            'is_read'  => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
