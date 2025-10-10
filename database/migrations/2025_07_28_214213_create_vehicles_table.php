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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->enum('brand', [
                'Mercedes-Benz',
                'Volkswagen',
                'BMW',
                'Audi',
                'Opel',
                'Skoda',
                'Ford',
                'Toyota',
                'Renault',
                'Hyundai',
                'Other'
            ]);
            $table->string('license_plate')->unique();
            $table->string('model');
            $table->enum('usage_type', ['taxi', 'rent']);
            $table->string('color')->nullable();
            $table->string('code')->nullable();
            $table->date('tuv_date')->nullable();
            $table->date('insurance_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'license_plate', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
