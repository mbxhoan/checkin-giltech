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
        Schema::create('card_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')
                ->nullable(false);
            $table->string('card_code')
                ->nullable(false);
            $table->string('type', 50)
                ->nullable(false)
                ->comment('FIELD/TEXT/QRCODE/IMAGE');
            $table->string('field', 255)
                ->nullable()
                ->comment('Field from client');
            $table->string('text', 255)
                ->default("TEXT")
                ->nullable();
            $table->integer('text_wrap')
                ->default(0)
                ->nullable(false);
            $table->string('img_path', 255)
                ->nullable();
            $table->decimal('pos_x', 8, 4)
                ->default(10)
                ->nullable(false);
            $table->decimal('pos_y', 8, 4)
                ->default(10)
                ->nullable(false);
            $table->decimal('size', 8, 4)
                ->default(300)
                ->nullable();
            $table->decimal('font_size', 8, 4)
                ->default(50)
                ->nullable();
            $table->string('font', 50)
                ->comment('font path')
                ->default('svn-arial/SVN-Arial-Bold.ttf')
                ->nullable();
            $table->decimal('width', 8, 4)
                ->default(300)
                ->nullable();
            $table->decimal('height', 8, 4)
                ->default(300)
                ->nullable();
            $table->boolean('bold')
                ->default(false)
                ->nullable(false);
            $table->boolean('italic')
                ->default(false)
                ->nullable(false);
            $table->string('color', 50)
                ->default('#000000')
                ->comment('Hex')
                ->nullable();
            $table->string('v_align', 255)
                ->default('top')
                ->nullable(false);
            $table->string('h_align', 255)
                ->default('left')
                ->nullable(false);
            $table->string('rotate', 50)
                ->default(0)
                ->nullable(false);
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('card_id', 'idx_card_id');

            /* Make foregin key */
            $table->foreign('card_id')
                ->references('id')
                ->on('cards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_details');
    }
};
