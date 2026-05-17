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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_default')
                ->nullable(false)
                ->default(false);
            $table->foreignId('event_id')
                ->nullable(false);
            $table->string('event_code', 200)
                ->nullable(false);
            $table->string('name', 255)
                ->nullable(false);
            $table->string('url', 255)
                ->nullable(false);
            $table->string('printer_url', 255)
                ->nullable(false);
            $table->string('printer', 255)
                ->nullable(false);
            $table->string('label', 200)
                ->nullable(false);
            $table->string('type', 50)
                ->default('NEW')
                ->nullable(false);
            $table->string('status', 50)
                ->default('NEW')
                ->nullable(false);
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->index('event_code', 'idx_event_code');

            /* Make foregin key */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('printers');
    }
};
