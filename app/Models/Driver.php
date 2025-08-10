<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use CommonTrait;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'work_model',
        'provision_model',
    ];

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
