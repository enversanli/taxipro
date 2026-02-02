<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_connections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->onDelete('cascade');

            $table->string('platform')->index();
            $table->string('platform_user_id')->nullable()->index();

            $table->text('access_token');
            $table->text('refresh_token')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();

            $table->json('meta_data')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_connections');
    }
};
