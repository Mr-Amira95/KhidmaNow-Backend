<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationBid extends Model
{
    protected $fillable = [
        'quotation_id',
        'provider_id',
        'price',
        'note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
