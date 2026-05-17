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
        Schema::create('print_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('printer_id')
                ->nullable(false);
            $table->string('key', 255)
                ->nullable(false);
            $table->string('name', 50)
                ->nullable(false);
            $table->string('label_file_name', 50)
                ->nullable(false);
            $table->string('ip_address', 50)
                ->nullable(false);
            $table->string('url', 255)
                ->nullable(false);
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('printer_id', 'idx_printer_id');

            /* Make foregin key */
            $table->foreign('printer_id')
                ->references('id')
                ->on('printers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('print_devices');
    }
};
