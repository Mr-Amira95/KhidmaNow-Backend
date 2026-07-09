<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'sub_category_id',
        'title',
        'description',
        'budget',
        'latitude',
        'longitude',
        'address',
        'scheduled_at',
        'status',
        'accepted_bid_id',
    ];

    protected function casts(): array
    {
        return [
            'budget'       => 'decimal:2',
            'latitude'     => 'decimal:8',
            'longitude'    => 'decimal:8',
            'scheduled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function bids()
    {
        return $this->hasMany(QuotationBid::class);
    }

    public function acceptedBid()
    {
        return $this->belongsTo(QuotationBid::class, 'accepted_bid_id');
    }

    public function track()
    {
        return $this->hasMany(QuotationTrack::class);
    }

    public function serviceRequest()
    {
        return $this->hasOne(ServiceRequest::class);
    }
}
