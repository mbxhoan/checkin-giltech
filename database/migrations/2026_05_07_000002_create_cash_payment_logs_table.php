<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cash_payment_logs')) {
            Schema::create('cash_payment_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->foreignId('payment_attempt_id')->nullable()->constrained('payment_attempts')->nullOnDelete();
                $table->unsignedInteger('cashier_user_id');
                $table->foreign('cashier_user_id')->references('id')->on('users');
                $table->decimal('amount_due', 15, 2);
                $table->decimal('amount_received', 15, 2);
                $table->decimal('change_amount', 15, 2)->default(0);
                $table->string('receipt_code', 100)->nullable()->unique();
                $table->text('note')->nullable();
                $table->timestamp('confirmed_at');
                $table->timestamp('voided_at')->nullable();
                $table->unsignedInteger('voided_by')->nullable();
                $table->foreign('voided_by')->references('id')->on('users')->nullOnDelete();
                $table->text('void_reason')->nullable();
                $table->timestamps();

                $table->index('event_id');
                $table->index('order_id');
                $table->index('payment_attempt_id');
                $table->index('cashier_user_id');
                $table->index('voided_at');
            });
            return;
        }

        Schema::table('cash_payment_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_payment_logs', 'cashier_user_id')) {
                $table->unsignedInteger('cashier_user_id')->after('payment_attempt_id');
            }

            if (!Schema::hasColumn('cash_payment_logs', 'voided_by')) {
                $table->unsignedInteger('voided_by')->nullable()->after('voided_at');
            }
        });

        DB::statement('ALTER TABLE cash_payment_logs MODIFY cashier_user_id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cash_payment_logs MODIFY voided_by INT UNSIGNED NULL');

        Schema::table('cash_payment_logs', function (Blueprint $table) {
            $table->foreign('cashier_user_id')->references('id')->on('users');
            $table->foreign('voided_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_payment_logs');
    }
};
