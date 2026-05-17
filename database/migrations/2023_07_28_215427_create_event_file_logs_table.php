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
        Schema::create('event_file_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('event_id')->nullable();
            $table->string('event_code', 200)->nullable();
            $table->string('name', 255)->nullable(false);
            $table->string('path', 255)->nullable(false);
            $table->string('type', 50)->default('FILE')->nullable();
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
        Schema::dropIfExists('event_file_logs');
    }
};
