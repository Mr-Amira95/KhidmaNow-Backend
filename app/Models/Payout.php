<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'provider_id',
        'service_request_id',
        'amount',
        'commission',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'commission' => 'decimal:2',
            'paid_at'    => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
