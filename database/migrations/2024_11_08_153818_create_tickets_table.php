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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')
                ->nullable();


            $table->string('event_code', 200)
                ->nullable(false);
            $table->string('code', 200)
                ->nullable(false);
            $table->string('name', 255)
                ->nullable(true);
            $table->string('type', 50)
                ->nullable(true);
            /* NOTE */
            $table->string('price', 50)
                ->nullable(false);
            /*****/
            $table->string('dates_string', 200)
                ->nullable(true);
            $table->json('dates_valid')
                ->nullable(true);
            $table->timestamps();

            $table->unique(['event_code', 'code']);

            $table->index('id', 'idx_id');
            $table->index('event_code', 'idx_event_code');
            $table->index('code', 'idx_code');
            $table->index('card_id', 'idx_card_id');

            $table->foreign('card_id')
                ->references('id')
                ->on('cards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
