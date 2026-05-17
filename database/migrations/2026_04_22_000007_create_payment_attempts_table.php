<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->unsignedInteger('attempt_no')->default(1);
            $table->string('gateway', 50)->default('onepay');
            $table->string('merchant_txn_ref', 100)->unique();
            $table->string('onepay_transaction_no', 100)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('VND');
            $table->string('status', 50)->default('created');
            $table->string('response_code', 20)->nullable();
            $table->string('response_message', 255)->nullable();
            $table->boolean('secure_hash_valid')->nullable();
            $table->boolean('amount_valid')->nullable();
            $table->boolean('merchant_valid')->nullable();
            $table->boolean('order_info_valid')->nullable();
            $table->boolean('order_state_valid')->nullable();
            $table->string('return_url', 255)->nullable();
            $table->string('callback_url', 255)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redirected_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('ipn_received_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('return_payload')->nullable();
            $table->json('callback_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('order_id', 'idx_payment_attempts_order_id');
            $table->index('registration_id', 'idx_payment_attempts_registration_id');
            $table->index('status', 'idx_payment_attempts_status');
            $table->index('gateway', 'idx_payment_attempts_gateway');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_attempts');
    }
};
