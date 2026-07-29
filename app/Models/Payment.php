<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'service_request_id',
        'amount',
        'payment_method',
        'status',
        'transaction_ref',
        'receipt_path',
        'rejection_reason',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'stripe_checkout_session_id',
        'stripe_checkout_url',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function recordWalletDebit(): void
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $this->user_id]);

        WalletTransaction::create([
            'wallet_id'   => $wallet->id,
            'type'        => 'debit',
            'amount'      => $this->amount,
            'source_type' => 'payment',
            'source_id'   => $this->id,
        ]);
    }
}
