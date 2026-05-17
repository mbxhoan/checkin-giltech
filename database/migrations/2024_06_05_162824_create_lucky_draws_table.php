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
        Schema::create('lucky_draws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable();
            $table->string('name', 255)
                ->nullable(false);
            $table->string('background_url_mobile', 255)
                ->nullable();
            $table->string('background_url_desktop', 255)
                ->nullable();
            $table->string('type', 50)
                ->default('RAFFLE')
                ->nullable(false);
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');

            /* Make foregin key */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
            $table->foreign('updated_by')
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
        Schema::dropIfExists('lucky_draws');
    }
};
