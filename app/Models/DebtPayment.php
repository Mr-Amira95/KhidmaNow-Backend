<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    protected $fillable = [
        'provider_id',
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

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function recordWalletCredit(): void
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $this->provider->user_id]);
        $wallet->increment('balance', $this->amount);

        WalletTransaction::create([
            'wallet_id'   => $wallet->id,
            'type'        => 'credit',
            'amount'      => $this->amount,
            'source_type' => 'debt_repayment',
            'source_id'   => $this->id,
        ]);
    }
}
