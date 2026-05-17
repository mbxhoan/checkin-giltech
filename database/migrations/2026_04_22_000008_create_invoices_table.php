<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->string('invoice_no', 100)->unique();
            $table->string('invoice_series', 50)->nullable();
            $table->string('status', 50)->default('draft');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('VND');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('registration_id', 'idx_invoices_registration_id');
            $table->index('status', 'idx_invoices_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
