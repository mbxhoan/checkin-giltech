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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->after('total_emails', function ($table) {
                $table->integer('limitation_per_time')
                    ->default(15)
                    ->nullable();

                $table->integer('hold_time')
                    ->default(10)
                    ->nullable();
            });

            $table->after('template_id', function ($table) {
                $table->boolean('is_online')
                    ->default(false)
                    ->nullable();
            });

            $table->after('bcc', function ($table) {
                $table->string('message_stream', 50)
                    ->default("outbound")
                    ->nullable(false);
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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('limitation_per_time');
            $table->dropColumn('hold_time');
            $table->dropColumn('is_online');
            $table->dropColumn('message_stream');
        });
    }
};
