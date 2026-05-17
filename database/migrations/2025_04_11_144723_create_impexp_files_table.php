<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('impexp_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable();
            $table->string('name', 255)
                ->nullable(false);
            $table->string('table', 50)
                ->nullable(false);
            $table->string('file_path', 255)
                ->nullable(false);
            $table->integer('total_record_before')
                ->default(0);
            $table->integer('total_record')
                ->default(0);
            $table->json('error_log')
                ->nullable();
            $table->string('type', 50)
                ->default('IMPORT');
            $table->string('status', 50)
                ->default('NEW');
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');

            /* Make foregin key */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impexp_files');
    }
};
