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
        Schema::create('language_defines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable(false);
            $table->foreignId('language_id')->nullable(false);
            $table->string('keyword', 100)->nullable(false);
            $table->string('translate', 255)->nullable(false);
            $table->string('type', 50)->default('TEXT')->nullable(false);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->index('language_id', 'idx_language_id');
            // $table->unique(['event_id', 'keyword']);
            $table->unique(['language_id', 'keyword']);

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
        Schema::dropIfExists('language_defines');
    }
};
