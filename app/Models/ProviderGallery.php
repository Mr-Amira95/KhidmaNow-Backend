<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderGallery extends Model
{
    protected $table = 'provider_gallery';

    protected $fillable = [
        'provider_id',
        'media_path',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
