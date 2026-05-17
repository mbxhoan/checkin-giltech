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
        Schema::create('campaign_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_file_id')
                ->nullable();
            $table->string('name', 255)
                ->nullable(false);
            $table->string('file_path', 255)
                ->nullable(false);
            $table->string('mime', 255)
                ->nullable(false);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('event_file_id', 'idx_event_file_id');
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');

            /* Make foregin key */
            $table->foreign('event_file_id')
                ->references('id')
                ->on('event_files');
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
        Schema::dropIfExists('campaign_attachments');
    }
};
