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
        Schema::create('countrys', function (Blueprint $table) {
            $table->id();
            $table->string('code', 200)->nullable(false);
            $table->string('name', 255)->nullable(false);
            $table->boolean('is_default')->nullable(false)->default(false);
            $table->string('description', 255)->nullable();
            $table->string('link_flag', 255)->nullable();
            $table->string('alt', 255)->nullable();
            $table->string('status', 50)->nullable(false)->default('ACTIVE');
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
        Schema::dropIfExists('countrys');
    }
};
