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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('invoice_detail_id')->nullable()->constrained('invoice_details')->nullOnDelete();

            // -- Platform Info --
            $table->string('platform')->index(); // 'bolt', 'uber'
            $table->string('external_id')->index();

            // -- Details --
            $table->text('pickup_address')->nullable();
            $table->text('dropoff_address')->nullable();
            $table->integer('distance_meters')->default(0);

            // -- Timestamps --
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('pickup_at')->nullable();
            $table->timestamp('dropoff_at')->nullable();

            // -- Financials --
            $table->decimal('gross_amount', 10, 2)->default(0);
            $table->decimal('net_earnings', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('tips', 10, 2)->default(0);
            $table->decimal('cash_collected', 10, 2)->default(0);

            $table->string('payment_method')->default('app');
            $table->string('status')->default('completed');
            $table->json('raw_data')->nullable();

            $table->timestamps();
            $table->unique(['platform', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
