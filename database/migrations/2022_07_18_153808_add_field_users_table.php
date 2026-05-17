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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->after('company_id', function ($table) {
                    $table->foreignId('event_id')
                        ->nullable();

                    /* Make index */
                    $table->index('id', 'idx_event_id');

                    /* Make foregin key */
                    $table->foreign('event_id')
                        ->references('id')
                        ->on('events')
                        ->onDelete('set null');
                });
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['event_id']); // Correct way

            $table->dropColumn(['event_id']); // Drop both columns
        });
    }
};
