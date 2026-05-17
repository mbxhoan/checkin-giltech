<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_item_id')->nullable()->constrained('registration_items')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('client_ticket_id')->nullable()->constrained('client_tickets')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('ticket_code', 100)->unique();
            $table->string('qr_code', 255)->unique();
            $table->string('status', 50)->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('registration_item_id', 'idx_ticket_issuances_registration_item_id');
            $table->index('order_id', 'idx_ticket_issuances_order_id');
            $table->index('client_ticket_id', 'idx_ticket_issuances_client_ticket_id');
            $table->index('ticket_id', 'idx_ticket_issuances_ticket_id');
            $table->index('status', 'idx_ticket_issuances_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_issuances');
    }
};
