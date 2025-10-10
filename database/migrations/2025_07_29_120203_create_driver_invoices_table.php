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
            $table->decimal('total_income', 10)->default(0);
            $table->decimal('gross', 10)->default(0);
            $table->decimal('bar', 10)->default(0);
            $table->decimal('tip', 10)->default(0);
            $table->decimal('net', 10)->default(0);
            $table->decimal('cash', 10)->default(0);
            $table->decimal('driver_salary', 10)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'driver_id', 'vehicle_id', 'year', 'month']);
        });

        Schema::create('invoice_details', function (Blueprint $table){
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->enum('platform', ['uber', 'bolt', 'bliq', 'freenow']);
            $table->decimal('gross', 10)->default(0);
            $table->decimal('tip', 10)->default(0);
            $table->decimal('bar', 10)->default(0);
            $table->decimal('cash', 10)->default(0);
            $table->decimal('net', 10)->default(0);
            $table->decimal('commission', 10)->default(0);
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
