<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('portal_user_id')->nullable()->constrained('portal_users')->nullOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('type', 50)->nullable();
            $table->string('subject', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('content')->nullable();
            $table->string('status', 50)->default('queued');
            $table->string('provider_message_id', 255)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('event_id', 'idx_email_logs_event_id');
            $table->index('portal_user_id', 'idx_email_logs_portal_user_id');
            $table->index('registration_id', 'idx_email_logs_registration_id');
            $table->index('order_id', 'idx_email_logs_order_id');
            $table->index('status', 'idx_email_logs_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_logs');
    }
};
