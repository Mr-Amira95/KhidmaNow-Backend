<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    protected $fillable = [
        'user_id',
        'favourite_item_id',
        'favourite_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
