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
        Schema::create('client_backups', function (Blueprint $table) {
            $table->id();
            $table->string('batch_key', 50)
                ->nullable(false);
            $table->unsignedBigInteger('event_id'); // Reference to the original client ID
            $table->unsignedBigInteger('country_id')->nullable(); // Reference to the original client ID
            $table->unsignedBigInteger('org_id'); // Reference to the original client ID
            $table->string('event_code');
            $table->string('qrcode')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('register_source')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps(); // Backup timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_backups');
    }
};
