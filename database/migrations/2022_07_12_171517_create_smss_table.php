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
        Schema::create('smss', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable(false);
            $table->foreignId('client_id')
                ->nullable(false);
            $table->datetime('send_time')
                ->nullable();
            $table->string('status', 50)
                ->default('NEW')
                ->nullable(false);
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->index('client_id', 'idx_client_id');

            /* Make foregin key */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('smss');
    }
};
