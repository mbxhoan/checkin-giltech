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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable(false);
            $table->string('event_code')
                ->nullable(false);
            $table->string('code', 200)
                ->nullable(false);
            // $table->string('title', 50)
            //     ->nullable();
            $table->string('client_type', 50)
                ->nullable();
            $table->string('file_name_template')
                ->nullable();
            $table->string('background', 255)
                ->nullable();
            $table->string('extension', 50)
                ->default('png')
                ->nullable();
            $table->integer('scaled')
                ->nullable();
            $table->string('type', 50)
                ->nullable()
                ->comment();
            $table->string('note', 255)
                ->nullable()
                ->comment();
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cards');
    }
};
