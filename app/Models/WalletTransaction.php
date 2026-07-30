<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'source_type',
        'source_id',
    ];

    private ?array $resolvedContext = null;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function requestTitle(): ?string
    {
        return $this->resolveContext()['title'];
    }

    public function transactionPaymentMethod(): ?string
    {
        return $this->resolveContext()['payment_method'];
    }

    /**
     * source_id points at different tables depending on source_type/type, since
     * wallet_transactions has no real polymorphic relation — see the WalletTransaction::create
     * call sites for each case.
     */
    private function resolveContext(): array
    {
        if ($this->resolvedContext !== null) {
            return $this->resolvedContext;
        }

        if ($this->source_type === 'payment' && $this->type === 'debit') {
            $payment = Payment::find($this->source_id);

            return $this->resolvedContext = [
                'title'          => $payment?->serviceRequest?->title,
                'payment_method' => $payment?->payment_method,
            ];
        }

        $serviceRequest = match (true) {
            in_array($this->source_type, ['payment', 'cash', 'commission'], true) => ServiceRequest::find($this->source_id),
            $this->source_type === 'payout' => Payout::find($this->source_id)?->serviceRequest,
            default => null,
        };

        return $this->resolvedContext = [
            'title'          => $serviceRequest?->title,
            'payment_method' => $serviceRequest?->payment?->payment_method,
        ];
    }
}
