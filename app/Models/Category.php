<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'icon',
        'is_active',
        'commission_rate',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }
}
