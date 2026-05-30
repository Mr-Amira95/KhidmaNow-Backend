<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderDocument extends Model
{
    protected $fillable = [
        'provider_id',
        'type',
        'document_url',
        'status',
        'rejection_reason',
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
