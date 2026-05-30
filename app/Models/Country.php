<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'flag',
        'name',
        'iso',
        'phone_code',
        'currency_code',
        'currency_value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'currency_value' => 'decimal:4',
            'is_active'      => 'boolean',
        ];
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}
