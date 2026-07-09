<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    public const TYPE_MODELS = [
        'category'     => Category::class,
        'sub_category' => SubCategory::class,
        'provider'     => Provider::class,
    ];

    public const TYPE_TABLES = [
        'category'     => 'categories',
        'sub_category' => 'sub_categories',
        'provider'     => 'providers',
    ];

    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
