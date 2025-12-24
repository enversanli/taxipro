<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->cascadeOnDelete();
            $table->year('year')->default(now()->format('Y'));
            $table->string('month')->default(now()->format('m'));

            // Taksimetre ve Genel Toplamlar
            $table->decimal('taxameter_total', 10, 2)->default(0); // Excel'deki 4.500 € (Genel Kasa)
            $table->decimal('sumup_payments', 10, 2)->default(0); // SumUp cihazı ile çekilenler

            // Şoför Maaş Hesaplama Parametreleri
            $table->integer('salary_percentage')->default(50); // Anteil %50
            $table->decimal('deductions_sb', 10, 2)->default(0); // Selbstbeteiligung (Hasar/Ceza kesintisi)
            $table->decimal('cash_withdrawals', 10, 2)->default(0); // Barentnahme (Şoförün kasadan aldığı avans)

            // Sonuç Alanları (Hesaplanmış veriler için)
            $table->decimal('net_salary', 10, 2)->default(0); // Şoföre ödenecek net tutar
            $table->decimal('expected_cash', 10, 2)->default(0); // Şoförün patrona teslim etmesi gereken nakit

            $table->timestamps();
            $table->unique(['company_id', 'driver_id', 'vehicle_id', 'year', 'month']);
        });

        Schema::create('invoice_details', function (Blueprint $table){
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['uber', 'bolt', 'bliq', 'freenow']);
            $table->unsignedTinyInteger('week_number')->nullable(); // Uber 1. hafta, 2. hafta ayrımı için

            $table->decimal('gross_amount', 10, 2)->default(0); // Uygulama toplam brüt
            $table->decimal('cash_collected_by_driver', 10, 2)->default(0); // Bar / Nakit ödemeler
            $table->decimal('tip', 10, 2)->default(0); // Trinkgeld
            $table->decimal('platform_commission', 10, 2)->default(0); // Netto hesabı için komisyon
            $table->decimal('net_payout', 10, 2)->default(0); // Şirket bankasına yatacak olan

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
        Schema::dropIfExists('invoices');
    }
};
