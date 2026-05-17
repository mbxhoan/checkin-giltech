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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable(false);
            $table->string('subject', 255)
                ->nullable();
            $table->string('email', 255)
                ->nullable();
            $table->longText('content')
                ->nullable();
            $table->datetime('sent_at')
                ->nullable();
            $table->string('from_name', 255)
                ->nullable();
            $table->string('from_email', 255)
                ->nullable();
            $table->string('to_name', 255)
                ->nullable();
            $table->string('to_email', 255)
                ->nullable();
            $table->string('status', 50)
                ->nullable();
            $table->timestamps();

            /* INDEX */
            $table->index('id', 'idx_id');
            $table->index('campaign_id', 'idx_campaign_id');

            /* FOREIGN */
            $table->foreign('campaign_id')->references('id')->on('campaigns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emails');
    }
};
