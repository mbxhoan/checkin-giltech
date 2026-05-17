<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('discount_type', 20)->default('percentage');
            $table->decimal('discount_value', 8, 2)->default(0);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 50)->default('ACTIVE');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'code'], 'uq_promo_codes_event_code');
            $table->index('status', 'idx_promo_codes_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('promo_codes');
    }
};
