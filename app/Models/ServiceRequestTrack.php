<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestTrack extends Model
{
    protected $table = 'service_request_track';

    protected $fillable = [
        'service_request_id',
        'from_status',
        'to_status',
        'changed_by',
        'date_time',
    ];

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
        ];
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
