<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'sub_category_id',
        'title',
        'description',
        'price',
        'status',
        'payment_status',
        'latitude',
        'longitude',
        'address',
        'note',
        'scheduled_at',
    ];

    protected function casts(): array
    {
        return [
            'price'        => 'decimal:2',
            'latitude'     => 'decimal:8',
            'longitude'    => 'decimal:8',
            'scheduled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function attachments()
    {
        return $this->hasMany(ServiceRequestAttachment::class);
    }

    public function track()
    {
        return $this->hasMany(ServiceRequestTrack::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function payout()
    {
        return $this->hasOne(Payout::class);
    }
}
