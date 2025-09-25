<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'platform',
        'total_income',
        'commission',
        'gross',
        'tip',
        'cash',
        'net',
        'bar',
        'date',
    ];
}
