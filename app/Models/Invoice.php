<?php

namespace App\Models;

use App\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;
    use CommonTrait;

    protected $fillable = [
        'company_id',
        'driver_id',
        'vehicle_id',
        'year',
        'month',
        // Taksimetre ve Genel Toplamlar
        'taxameter_total',
        'sumup_payments',
        // Maaş ve Kesinti Parametreleri
        'salary_percentage',
        'deductions_sb',
        'cash_withdrawals', // şoförün aldığu avans, maaştan önce alınan
        // Hesaplanan Sonuçlar
        'driver_salary',
        'expected_cash',
        // Eski alanlardan uyumlu olanlar (Opsiyonel - Migration'da varsa tutulabilir)
        'gross',
        'tip',
        'bar',
        'net',
    ];

    /**
     * Casts: Veritabanından gelen rakamların float/decimal
     * olarak doğru işlenmesini sağlar.
     */
    protected $casts = [
        'taxameter_total' => 'decimal:2',
        'driver_salary' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'deductions_sb' => 'decimal:2',
        'year' => 'integer',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function vouchers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Voucher::class, 'invoice_id');
    }

    public function expenses()
    {
        return $this->hasMany(\App\Models\Expense::class);
    }
}
