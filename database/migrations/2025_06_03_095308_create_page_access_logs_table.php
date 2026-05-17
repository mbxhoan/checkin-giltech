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
        Schema::create('page_access_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lp_id')
                ->nullable();
            $table->string('page')
                ->index(); // e.g., route name or URL
            $table->ipAddress('ip_address')
                ->nullable(); // Optional: IP logging
            $table->unsignedBigInteger('user_id')
                ->nullable(); // Optional: Authenticated user
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_access_logs');
    }
};
