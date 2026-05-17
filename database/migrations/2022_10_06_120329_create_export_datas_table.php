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
        Schema::create('export_datas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable(false);
            $table->integer('user_id')
                ->unsigned()
                ->nullable(false);
            $table->string('status', 50)
                ->default('EXPORTED')
                ->nullable(false);
            $table->string('type', 50)
                ->default('EXPORT_CLIENT')
                ->nullable()
                ->comment('EXPORT_CLIENT/EXPORT_CHECKIN');
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');

            $table->foreign('event_id')
                ->references('id')
                ->on('events');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('export_datas');
    }
};
