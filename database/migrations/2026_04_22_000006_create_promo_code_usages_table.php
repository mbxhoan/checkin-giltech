<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained('promo_codes')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('portal_user_id')->nullable()->constrained('portal_users')->nullOnDelete();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->timestamp('applied_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('order_id', 'uq_promo_code_usages_order_id');
            $table->index('promo_code_id', 'idx_promo_code_usages_promo_code_id');
            $table->index('registration_id', 'idx_promo_code_usages_registration_id');
            $table->index('portal_user_id', 'idx_promo_code_usages_portal_user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('promo_code_usages');
    }
};
