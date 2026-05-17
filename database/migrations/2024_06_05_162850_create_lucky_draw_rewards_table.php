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
        Schema::create('lucky_draw_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lucky_draw_id')
                ->nullable();
            $table->boolean('is_given')
                ->default(false)
                ->nullable(false);
            $table->string('code', 200)
                ->unique()
                ->nullable(false);
            $table->string('name', 255)
                ->nullable(false);
            $table->string('img_link')
                ->nullable();
            $table->string('value', 255)
                ->nullable();
            $table->integer('order')
                ->nullable();
            $table->string('order_name', 255)
                ->nullable(false);
            $table->integer('time')
                ->nullable(0)
                ->nullable(false);
            $table->float('probability')
                ->nullable();
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('code', 'idx_code');
            $table->index('lucky_draw_id', 'idx_lucky_draw_id');

            /* Make foregin key */
            $table->foreign('lucky_draw_id')
                ->references('id')
                ->on('lucky_draws');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lucky_draw_rewards');
    }
};
