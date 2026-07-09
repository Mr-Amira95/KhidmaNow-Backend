<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = [
        'user_id',
        'city_id',
        'business_name',
        'description',
        'experience_years',
        'availability_status',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function documents()
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function gallery()
    {
        return $this->hasMany(ProviderGallery::class);
    }

    public function subCategories()
    {
        return $this->hasMany(ProviderSubCategory::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function quotationBids()
    {
        return $this->hasMany(QuotationBid::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function chatbotRooms()
    {
        return $this->hasMany(ChatbotRoom::class);
    }
}
