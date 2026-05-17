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
        Schema::create('print_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printer_id')
                ->nullable(false);
            $table->string('file_path', 255)
                ->nullable(false);
            $table->integer('created_by')
                ->unsigned()
                ->nullable();
            $table->integer('updated_by')
                ->unsigned()
                ->nullable();
            $table->string('type', 50)
                ->default('NEW')
                ->nullable(false);
            $table->string('status', 50)
                ->default('NEW')
                ->nullable(false);
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('printer_id', 'idx_printer_id');
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');

            /* Make foregin key */
            $table->foreign('printer_id')
                ->references('id')
                ->on('printers');
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
        Schema::dropIfExists('print_logs');
    }
};
