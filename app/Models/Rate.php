<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    protected $fillable = [
        'service_request_id',
        'rater_id',
        'ratee_id',
        'rating_type',
        'rate',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:1',
        ];
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratee()
    {
        return $this->belongsTo(User::class, 'ratee_id');
    }
}
