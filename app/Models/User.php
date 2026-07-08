<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'profile_image',
        'user_type',
        'average_rating',
        'ratings_count',
        'status',
        'latitude',
        'longitude',
        'address',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password'       => 'hashed',
            'average_rating' => 'decimal:1',
            'latitude'       => 'decimal:8',
            'longitude'      => 'decimal:8',
            'last_login_at'  => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->hasOne(Provider::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function givenRates()
    {
        return $this->hasMany(Rate::class, 'rater_id');
    }

    public function receivedRates()
    {
        return $this->hasMany(Rate::class, 'ratee_id');
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
