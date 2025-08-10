<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Driver extends Model
{
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

    protected static function booted()
    {
        static::addGlobalScope('userCompany', function (Builder $builder) {
            if (Auth::user()->isAdmin) {
                $builder->where('id', $user->company_id);
            }
        });
    }
}
