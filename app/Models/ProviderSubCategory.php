<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderSubCategory extends Model
{
    protected $fillable = [
        'provider_id',
        'sub_category_id',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }
}
