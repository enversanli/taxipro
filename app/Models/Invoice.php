<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;
    use CommonTrait;

    protected $fillable = [
        'company_id',
        'driver_id',
        'vehicle_id',
        'month',
        'year',
        'total_income',
        'gross',
        'tip',
        'bar',
        'net',
        'cash',
        'driver_salary'
    ];

    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function details(): HasMany{
        return  $this->hasMany(InvoiceDetail::class);
    }
}
