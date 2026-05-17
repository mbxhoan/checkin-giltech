<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_tickets', function (Blueprint $table) {
            $table->after('ticket_id', function ($table) {
                $table->foreignId('order_id')
                    ->nullable();

                $table->index('order_id', 'idx_order_id');

                $table->foreign('order_id')
                    ->references('id')
                    ->on('orders');
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_tickets', function (Blueprint $table) {
            $table->dropForeign('client_tickets_order_id_foreign');
            $table->dropIndex('idx_order_id');
            $table->dropColumn('order_id');
        });
    }
};
