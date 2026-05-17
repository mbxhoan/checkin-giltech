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
        Schema::table('emails', function (Blueprint $table) {
            $table->after('email', function ($table) {
                $table->string('qrcode', 200)
                    ->nullable();

                $table->index('qrcode', 'idx_qrcode');
            });

            $table->string('message_id', 255)
                    ->nullable()
                    ->after('campaign_id');
            $table->json('server_response')
                    ->nullable()
                    ->after('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropIndex('idx_qrcode');
            $table->dropColumn('qrcode');

            $table->dropColumn('message_id');
            $table->dropColumn('server_response');
        });
    }
};
