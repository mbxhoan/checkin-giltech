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
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->after('qrcode', function ($table) {
                    $table->string('img_qrcode', 255)
                        ->nullable();
                });
            });
        }
        if (Schema::hasTable('emails')) {
            Schema::table('emails', function (Blueprint $table) {
                $table->after('client_id', function ($table) {
                    $table->string('subject', 255)
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

    }
};
