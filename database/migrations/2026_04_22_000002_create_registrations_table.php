<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_user_id')->constrained('portal_users')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('current_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('code', 100)->unique();
            $table->string('status', 50)->default('draft');
            $table->string('checkin_sync_status', 50)->default('pending');
            $table->string('checkin_reference', 100)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('checkin_synced_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'portal_user_id'], 'uq_registrations_event_portal_user');
            $table->index('status', 'idx_registrations_status');
            $table->index('checkin_sync_status', 'idx_registrations_checkin_sync_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('registrations');
    }
};
