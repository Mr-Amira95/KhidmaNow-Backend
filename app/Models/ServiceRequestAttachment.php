<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestAttachment extends Model
{
    protected $fillable = [
        'service_request_id',
        'url',
        'type',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
