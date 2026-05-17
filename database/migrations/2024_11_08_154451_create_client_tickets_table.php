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
        Schema::create('client_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable();
            $table->foreignId('client_id')
                ->nullable();
            $table->foreignId('ticket_id')
                ->nullable(false);
            $table->boolean('is_link')
                ->default(false)
                ->nullable(false);
            $table->string('img_path', 255)
                ->nullable();
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->index('client_id', 'idx_client_id');
            $table->index('ticket_id', 'idx_ticket_id');

            $table->foreign('event_id')
                ->references('id')
                ->on('events');
            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->foreign('ticket_id')
                ->references('id')
                ->on('tickets')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_tickets');
    }
};
