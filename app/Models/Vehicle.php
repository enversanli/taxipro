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

    protected $casts = [
        'tuv_date' => 'date',
        'insurance_date' => 'date', // Good practice to add this too
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
            return __('common.unknown');
        }

        $date = Carbon::parse($date)->addYear();
        $now = Carbon::now();

        if ($date->isBefore($now)) {
            return __('common.expired');
        }

        if ($date->between($now->copy()->subDay(), $now->copy()->addMonths(2))) {
            return __('common.soon');
        }

        return __('common.upcoming');
    }


}
