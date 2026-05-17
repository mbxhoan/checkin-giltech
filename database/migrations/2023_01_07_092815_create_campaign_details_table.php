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
        Schema::create('campaign_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable(false);
            $table->foreignId('tag_id')->nullable();
            $table->string('name', 255)
                ->nullable(false);
            $table->string('qrcode', 200)
                ->unique();
            $table->string('gender', 20)
                ->nullable();
            $table->string('email', 255)
                ->nullable();
            $table->string('phone', 15)
                ->nullable();
            $table->boolean('send_email')
                ->default(0);
            $table->boolean('send_zalo')
                ->default(0);
            $table->boolean('send_sms')
                ->default(0);
            $table->string('status', 50)
                ->nullable();
            $table->timestamps();

            /* INDEX */
            $table->index('id', 'idx_id');
            $table->index('campaign_id', 'idx_campaign_id');
            $table->index('tag_id', 'idx_tag_id');

            /* FOREIGN */
            $table->foreign('campaign_id')->references('id')->on('campaigns');
            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_details');
    }
};
