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
        Schema::table('print_devices', function (Blueprint $table) {
            $table->after('printer_id', function ($table) {
                $table->foreignId('label_id')
                    ->nullable();

                $table->index('label_id', 'idx_label_id');

                /* Make foregin key */
                $table->foreign('label_id')
                    ->references('id')
                    ->on('labels');
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
        Schema::table('print_devices', function (Blueprint $table) {
            $table->dropForeign(['label_id']);
            $table->dropIndex('idx_label_id');
            $table->dropColumn('label_id');
        });
    }
};
