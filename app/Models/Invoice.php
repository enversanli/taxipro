<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'month',
        'year',
        'total_income',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(){
        return $this->hasOne(Vehicle::class);
    }

    public function details(): HasMany{
        return  $this->hasMany(InvoiceDetail::class);
    }
}
