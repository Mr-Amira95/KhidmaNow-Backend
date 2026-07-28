<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCliqDetail extends Model
{
    protected $table = 'company_cliq_details';

    protected $fillable = [
        'alias',
        'bank_name',
        'holder_name',
    ];
}
