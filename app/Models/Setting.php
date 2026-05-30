<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'country_id',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
