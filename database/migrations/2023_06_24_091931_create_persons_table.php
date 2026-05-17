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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable(false);
            $table->string('event_code', 200)
                ->nullable();
            $table->string('company_name', 255)
                ->nullable();
            $table->string('name', 255)
                ->nullable(false);
            $table->string('code', 200)
                ->nullable(false);
            $table->string('gender', 20)
                ->nullable();
            $table->string('title', 20)
                ->nullable();
            $table->string('email', 255)
                ->nullable();
            $table->string('phone', 15)
                ->nullable();
            $table->string('type', 100)
                ->nullable();
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable();
            $table->timestamps();

            /* INDEX */
            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');

            /* FOREIGN */
            $table->foreign('event_id')->references('id')->on('events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('persons');
    }
};
