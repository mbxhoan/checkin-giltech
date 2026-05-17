<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('portal_user_id')->nullable()->constrained('portal_users')->nullOnDelete();
            $table->foreignId('registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->foreignId('promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete();
            $table->string('currency', 3)->default('VND');
            $table->decimal('subtotal_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('checkin_sync_status', 50)->default('pending');
            $table->string('checkin_reference', 100)->nullable();
            $table->json('metadata')->nullable();

            $table->index('portal_user_id', 'idx_orders_portal_user_id');
            $table->index('registration_id', 'idx_orders_registration_id');
            $table->index('event_id', 'idx_orders_event_id');
            $table->index('promo_code_id', 'idx_orders_promo_code_id');
            $table->index('checkin_sync_status', 'idx_orders_checkin_sync_status');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['portal_user_id']);
            $table->dropForeign(['registration_id']);
            $table->dropForeign(['event_id']);
            $table->dropForeign(['promo_code_id']);

            $table->dropIndex('idx_orders_portal_user_id');
            $table->dropIndex('idx_orders_registration_id');
            $table->dropIndex('idx_orders_event_id');
            $table->dropIndex('idx_orders_promo_code_id');
            $table->dropIndex('idx_orders_checkin_sync_status');

            $table->dropColumn([
                'portal_user_id',
                'registration_id',
                'event_id',
                'promo_code_id',
                'currency',
                'subtotal_amount',
                'discount_amount',
                'tax_amount',
                'total_amount',
                'paid_at',
                'cancelled_at',
                'refunded_at',
                'checkin_sync_status',
                'checkin_reference',
                'metadata',
            ]);
        });
    }
};
