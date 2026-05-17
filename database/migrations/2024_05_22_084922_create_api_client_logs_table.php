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
        Schema::create('api_client_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method', 50)
                ->nullable(false);
            $table->string('endpoint', 200)
                ->nullable(false);
            $table->json('request')
                ->nullable(false);
            $table->json('response')
                ->nullable(false);
            $table->string('user_agent', 255)
                ->nullable();
            $table->string('status', 50)
                ->default('NEW')
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
        Schema::dropIfExists('api_client_logs');
    }
};
