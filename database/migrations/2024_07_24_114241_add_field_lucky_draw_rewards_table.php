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
        Schema::table('lucky_draw_rewards', function (Blueprint $table) {
            $table->after('lucky_draw_id', function ($table) {
                $table->integer('assignee_id')
                    ->nullable();
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
        Schema::table('lucky_draw_rewards', function (Blueprint $table) {
            $table->dropColumn('assignee_id');
        });
    }
};
