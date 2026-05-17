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
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('qrcode', 200);
            $table->string('client_name', 255)->nullable();
            $table->string('source', 50)->nullable();
            $table->datetime('scan_time');
            $table->text('note')->nullable();
            $table->string('status', 50)->default('NEW');
            $table->timestamps();

            /* Indexes */
            $table->index('event_id', 'idx_event_id');
            $table->index('user_id', 'idx_user_id');

            /* Foreign Keys */
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkins');
    }
};
