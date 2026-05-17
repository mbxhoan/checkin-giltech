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
        Schema::create('event_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable(false);
            $table->foreignId('language_id')->nullable(false);
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->index('language_id', 'idx_language_id');

            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('language_id')->references('id')->on('languages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_languages');
    }
};
