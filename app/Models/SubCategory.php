<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function providerSubCategories()
    {
        return $this->hasMany(ProviderSubCategory::class);
    }

    public function providers()
    {
        return $this->hasManyThrough(
            Provider::class,
            ProviderSubCategory::class,
            'sub_category_id',
            'id',
            'id',
            'provider_id'
        );
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
