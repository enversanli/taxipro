<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'platform',
        'week_number', // Hafta veya periyot bilgisi için
        'gross_amount', // Uygulama üzerindeki toplam brüt (Excel: Brutto)
        'cash_collected_by_driver', // Şoförün yolcudan aldığı nakit (Excel: Bar)
        'tip', // Bahşiş (Excel: Trinkgeld)
        'platform_commission', // Uygulamanın kestiği komisyon
        'net_payout', // Şirket banka hesabına yatan net tutar (Excel: Netto)
        // Eğer hala ihtiyaç duyuyorsanız eski alanları aşağıda tutabilirsiniz:
        'cash',
        'net',
        'bar',
    ];

    /**
     * Rakamların float olarak dönmesi için cast ekliyoruz.
     */
    protected $casts = [
        'gross_amount' => 'decimal:2',
        'cash_collected_by_driver' => 'decimal:2',
        'tip' => 'decimal:2',
        'platform_commission' => 'decimal:2',
        'net_payout' => 'decimal:2',
    ];

    /**
     * Bu detayın bağlı olduğu ana fatura kaydı.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
