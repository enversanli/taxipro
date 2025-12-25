<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'company_id',
        'vehicle_id',
        'driver_id',
        'invoice_id',
        'type',
        'amount',
        'date',
        'receipt_path',
        'description',
        'note'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    // HATANIN MUHTEMEL KAYNAĞI BURASI:
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
