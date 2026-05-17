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
        Schema::create('webhook_postmarks', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 200)->nullable();
            $table->string('server_id', 255)->nullable();
            $table->string('message_id', 255)->nullable(false);
            $table->string('message_stream', 50)->nullable(false);
            $table->string('email', 255)->nullable(false);
            $table->string('tag', 50)->nullable();
            $table->string('details', 255)->nullable();
            $table->dateTime('record_time')->nullable();
            $table->string('status', 50)->nullable(false);
            $table->json('metadata')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index('message_id', 'idx_message_id');
            $table->index('email', 'idx_email');
            $table->index('server_id', 'idx_server_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhook_postmarks');
    }
};
