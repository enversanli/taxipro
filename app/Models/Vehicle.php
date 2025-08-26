<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use CommonTrait;

    protected $fillable = [
        'company_id',
        'license_plate',
        'model',
        'usage_type',
        'color',
        'brand',
        'tuv_date',
        'insurance_date',
        'code',
    ];

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function getUpcomingTuvDateAttribute()
    {
        if (!$this->tuv_date) {
            return null;
        }

        return now()->parse($this->tuv_date)->addYear()->toDateString();
    }

    public function getUpcomingInsuranceDateAttribute(): ?string
    {
        if (!$this->tuv_date) {
            return null;
        }

        return now()->parse($this->tuv_date)->addYear()->toDateString();
    }

    public function getTuvStatusAttribute(): string
    {
        return $this->getStatus($this->tuv_date);
    }

    public function getInsuranceStatusAttribute(): string
    {
        return $this->getStatus($this->insurance_date);
    }

    private function getStatus($date): string
    {
        if (!$date) {
            return 'UNKNOWN';
        }

        $date = Carbon::parse($date)->addYear();
        $now = Carbon::now();

        if ($date->format('Y-m-d') < $now->format('Y-m-d')) {
            return 'EXPIRED';
        }

        if ($date->between($now->copy()->subDay(), $now->copy()->addMonths(2))) {
            return 'SOON';
        }

        return 'UPCOMING';
    }


}
