<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationTrack extends Model
{
    protected $table = 'quotation_track';

    protected $fillable = [
        'quotation_id',
        'from_status',
        'to_status',
        'changed_by',
        'date_time',
    ];

    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
        ];
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
