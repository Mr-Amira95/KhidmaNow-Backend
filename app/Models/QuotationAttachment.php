<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationAttachment extends Model
{
    protected $fillable = [
        'quotation_id',
        'url',
        'type',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
