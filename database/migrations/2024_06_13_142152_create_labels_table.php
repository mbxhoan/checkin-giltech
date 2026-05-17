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
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable(false);
            $table->boolean('is_default')
                ->default(false)
                ->nullable(false);
            $table->string('name', 255)
                ->nullable(false);
            $table->decimal('width', 8, 4)
                ->default()
                ->nullable(false);
            $table->decimal('height', 8, 4)
                ->default()
                ->nullable(false);
            $table->string('unit', 50)
                ->default('%')
                ->nullable(false);
            $table->string('type', 50)
                ->default()
                ->nullable();
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->timestamps();

            /* INDEX */
            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');

            /* FOREIGN */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('labels');
    }
};
