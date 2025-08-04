<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vehicle extends Model
{
    protected $fillable = [
        'company_id',
        'license_plate',
        'model',
        'usage_type',
        'color',
        'brand',
        'tüv_date',
        'insurance_date',
        'code',
    ];

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class);
    }

    public function company(): BelongsTo {
        return  $this->belongsTo(Company::class);
    }
}
