<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    protected $fillable = [
        'invoice_id',
        'platform',
        'total_income',
        'gross',
        'tip',
        'cash',
        'net',
        'bar',
        'date',
    ];
}
