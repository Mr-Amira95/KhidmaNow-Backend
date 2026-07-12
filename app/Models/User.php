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
        'google_id',
        'apple_id',
        'password',
        'profile_image',
        'user_type',
        'is_super_admin',
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
            'is_super_admin' => 'boolean',
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasPermission(string $key): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('key', $key))
            ->exists();
    }

    public function permissionKeys()
    {
        if ($this->is_super_admin) {
            return Permission::pluck('key');
        }

        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn ($role) => $role->permissions->pluck('key'))
            ->unique()
            ->values();
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

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
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

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function chatbotRooms()
    {
        return $this->hasMany(ChatbotRoom::class);
    }
}
