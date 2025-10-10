<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleExpense extends Model
{
    protected $fillable = [
        'vehicle_id',
        'type',
        'amount',
        'date',
        'receipt_path',
        'description',
        'note',
    ];

    // Add this:
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
