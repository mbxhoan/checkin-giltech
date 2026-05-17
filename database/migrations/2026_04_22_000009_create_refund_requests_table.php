<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('requested_by_portal_user_id')->nullable()->constrained('portal_users')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 50)->default('requested');
            $table->text('reason')->nullable();
            $table->string('external_ref', 100)->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('order_id', 'idx_refund_requests_order_id');
            $table->index('registration_id', 'idx_refund_requests_registration_id');
            $table->index('requested_by_portal_user_id', 'idx_refund_requests_requested_by_portal_user_id');
            $table->index('status', 'idx_refund_requests_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('refund_requests');
    }
};
