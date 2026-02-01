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
        Schema::create('platform_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable(); // If you have multi-tenancy
            $table->string('platform')->index();

            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->text('credentials')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();

            $table->boolean('is_active')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_connections');
    }
};
