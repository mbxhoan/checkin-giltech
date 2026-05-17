<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_id', 40)->unique();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('portal_user_id')->nullable()->constrained('portal_users')->nullOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('replaced_by_id')->nullable()->constrained('registration_files')->nullOnDelete();
            $table->string('field_key', 100);
            $table->string('owner_email', 255)->nullable();
            $table->string('disk', 50)->default('local');
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('extension', 20);
            $table->string('mime_type', 120);
            $table->unsignedInteger('size_bytes');
            $table->string('sha256', 64)->nullable();
            $table->string('status', 30)->default('temp');
            $table->string('uploaded_by_type', 40)->default('public_registration');
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('attached_at')->nullable();
            $table->timestamp('replaced_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status'], 'idx_registration_files_event_status');
            $table->index(['registration_id', 'field_key', 'status'], 'idx_registration_files_registration_field_status');
            $table->index(['portal_user_id', 'status'], 'idx_registration_files_portal_status');
            $table->index(['owner_email', 'event_id'], 'idx_registration_files_owner_email_event');
            $table->index('expires_at', 'idx_registration_files_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_files');
    }
};
