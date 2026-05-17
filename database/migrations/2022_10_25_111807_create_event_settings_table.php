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
        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->nullable();
            $table->foreignId('event_id')
                ->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->string('description', 255)->nullable();
            $table->text('value')->nullable();
            $table->json('options')->nullable();
            $table->string('group', 50)->nullable();
            $table->string('input_type', 50)->default('text');
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->index('parent_id', 'idx_parent_id');
            $table->unique(['event_id', 'name', 'group']);

            $table->foreign('event_id')
                ->references('id')
                ->on('events');
            $table->foreign('parent_id')
                ->references('id')
                ->on('event_settings')
                ->ondelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_settings');
    }
};
