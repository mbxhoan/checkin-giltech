<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tối ưu query chính: WHERE event_id=X AND status!='DELETED' ORDER BY updated_at DESC
        Schema::table('clients', function (Blueprint $table) {
            $table->index(['event_id', 'status', 'updated_at'], 'idx_clients_event_status_updated');
        });

        // Tối ưu correlated subqueries lấy first_checkin_at / first_checkout_at
        // và filter checked_in / findCheckin()
        Schema::table('checkins', function (Blueprint $table) {
            $table->index(['event_id', 'qrcode', 'type', 'status'], 'idx_checkins_event_qrcode_type_status');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('idx_clients_event_status_updated');
        });

        Schema::table('checkins', function (Blueprint $table) {
            $table->dropIndex('idx_checkins_event_qrcode_type_status');
        });
    }
};
