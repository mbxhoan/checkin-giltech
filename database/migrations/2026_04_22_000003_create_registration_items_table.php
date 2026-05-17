<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('registration_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('ticket_code', 200)->nullable();
            $table->string('ticket_name', 255)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status', 50)->default('ACTIVE');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('registration_id', 'idx_registration_items_registration_id');
            $table->index('order_id', 'idx_registration_items_order_id');
            $table->index('ticket_id', 'idx_registration_items_ticket_id');
            $table->index('status', 'idx_registration_items_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('registration_items');
    }
};
