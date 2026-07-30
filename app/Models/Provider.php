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
        'suspended_at',
        'suspended_until',
        'suspension_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_verified'      => 'boolean',
            'suspended_at'     => 'datetime',
            'suspended_until'  => 'datetime',
        ];
    }

    public function isSuspended(): bool
    {
        return $this->isTimeSuspended() || $this->isDebtSuspended();
    }

    public function isTimeSuspended(): bool
    {
        if ($this->suspended_at === null) {
            return false;
        }

        // A null suspended_until means the suspension has no auto-expiry and
        // stays in effect until an admin explicitly unsuspends the provider.
        return $this->suspended_until === null || $this->suspended_until->isFuture();
    }

    public function isDebtSuspended(): bool
    {
        $threshold = $this->debtSuspensionThreshold();

        return $threshold > 0 && $this->debtAmount() >= $threshold;
    }

    public function debtAmount(): float
    {
        $balance = (float) ($this->user?->wallet?->balance ?? 0);

        return $balance < 0 ? abs($balance) : 0.0;
    }

    private function debtSuspensionThreshold(): float
    {
        $setting = Setting::where('key', 'provider_debt_suspension_threshold')->first();

        return $setting ? (float) $setting->value : 0.0;
    }

    public function scopeNotSuspended($query)
    {
        $query->where(function ($q) {
            $q->whereNull('suspended_at')
                ->orWhere(function ($q2) {
                    $q2->whereNotNull('suspended_until')->where('suspended_until', '<=', now());
                });
        });

        $threshold = (new static())->debtSuspensionThreshold();
        if ($threshold > 0) {
            $query->whereDoesntHave('user.wallet', function ($q) use ($threshold) {
                $q->where('balance', '<=', -$threshold);
            });
        }

        return $query;
    }

    public function isOnline(): bool
    {
        return $this->availability_status === 'online';
    }

    public function scopeOnline($query)
    {
        return $query->where('availability_status', 'online');
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

    public function feedbacks()
    {
        return $this->hasMany(Rate::class, 'ratee_id', 'user_id')
            ->where('rating_type', 'provider')
            ->latest();
    }
}
