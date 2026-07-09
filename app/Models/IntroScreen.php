<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntroScreen extends Model
{
    protected $fillable = [
        'image',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order'     => 'integer',
        ];
    }
}
