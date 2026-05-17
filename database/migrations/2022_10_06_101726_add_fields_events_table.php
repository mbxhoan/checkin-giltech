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
        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                $table->after('main_bg_mobile', function ($table) {
                    $table->string('main_bglandingpage_desktop', 255)
                        ->nullable();
                    $table->string('main_bglandingpage_mobile', 255)
                        ->nullable();
                    $table->string('sound_success', 255)
                        ->nullable();
                    $table->string('sound_fail', 255)
                        ->nullable();
                    $table->json('custom_checkin_messages')
                        ->nullable();
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
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};
