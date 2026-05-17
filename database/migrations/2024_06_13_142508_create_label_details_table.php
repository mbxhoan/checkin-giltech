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
        Schema::create('label_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_id')
                ->nullable(false);
            $table->string('field', 255)
                ->nullable(false);
            $table->string('type', 50)
                ->nullable();
            $table->decimal('pos_x', 8, 4)
                ->default(10)
                ->nullable(false);
            $table->decimal('pos_y', 8, 4)
                ->default(10)
                ->nullable(false);
            $table->string('v_align', 50)
                ->default('left')
                ->nullable(false);
            $table->string('h_align', 50)
                ->default('top')
                ->nullable(false);
            $table->string('color', 50)
                ->default('#000000')
                ->nullable(false);
            $table->string('font', 200)
                ->nullable();
            $table->decimal('size', 8, 4)
                ->default(15)
                ->nullable(false);
            $table->string('unit', 50)
                ->default('px')
                ->nullable(false);
            $table->string('width', 50)
                ->default('50')
                ->nullable(false);
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->timestamps();

            /* INDEX */
            $table->index('id', 'idx_id');
            $table->index('label_id', 'idx_label_id');

            /* FOREIGN */
            $table->foreign('label_id')
                ->references('id')
                ->on('labels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('label_details');
    }
};
