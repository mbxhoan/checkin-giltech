<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_file_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_file_id')->constrained('registration_files')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('portal_user_id')->nullable()->constrained('portal_users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('actor_type', 50)->nullable();
            $table->string('actor_ref', 120)->nullable();
            $table->string('message', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'action'], 'idx_registration_file_logs_event_action');
            $table->index('portal_user_id', 'idx_registration_file_logs_portal_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_file_logs');
    }
};
