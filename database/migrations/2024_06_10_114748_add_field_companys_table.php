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
        Schema::table('companys', function (Blueprint $table) {
            $table->after('name', function ($table) {
                $table->string('license', 255)
                    ->nullable();
                $table->json('languages')
                    ->nullable();
                $table->json('settings')
                    ->nullable();
                $table->json('devices')
                    ->nullable();
                $table->json('templates')
                    ->nullable();
                $table->json('senders')
                    ->nullable();
                $table->string('type', 200)
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
        Schema::table('companys', function (Blueprint $table) {
            $table->dropColumn('license');
            $table->dropColumn('languages');
            $table->dropColumn('settings');
            $table->dropColumn('devices');
            $table->dropColumn('type');
        });
    }
};
