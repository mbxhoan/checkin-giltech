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
        Schema::create('event_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable(false);
            $table->integer('media_id')
                ->unsigned()
                ->nullable();
            $table->string('name', 255)
                ->nullable(false);
            $table->string('file_path', 255)
                ->unique();
            $table->boolean('is_public')
                ->nullable(false)
                ->default(true);
            $table->string('type', 50)
                ->nullable(false)
                ->default('FILE');
            $table->string('status', 50)
                ->nullable(false)
                ->default('ACTIVE');
            $table->timestamps();

            /* INDEX */
            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->index('media_id', 'idx_media_id');

            /* FOREIGN */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
            $table->foreign('media_id')
                ->references('id')
                ->on('media')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_files');
    }
};
