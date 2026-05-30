<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'icon',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function providerSubCategories()
    {
        return $this->hasMany(ProviderSubCategory::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
