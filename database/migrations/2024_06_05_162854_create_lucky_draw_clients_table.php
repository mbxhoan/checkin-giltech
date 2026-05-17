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
        Schema::create('lucky_draw_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_id')
                ->nullable();
            $table->foreignId('lucky_draw_id')
                ->nullable(false);
            $table->string('name', 255)
                ->nullable(false);
            $table->string('qrcode', 200)
                ->nullable(false);
            $table->string('email', 255)
                ->nullable();
            $table->string('type', 50)
                ->default('NEW')
                ->nullable();
            $table->json('custom_fields')
                ->nullable();
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('qrcode', 'idx_qrcode');
            $table->index('reward_id', 'idx_reward_id');
            $table->index('lucky_draw_id', 'idx_lucky_draw_id');

            /* Make foregin key */
            $table->foreign('reward_id')
                ->references('id')
                ->on('lucky_draw_rewards');
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
        Schema::dropIfExists('lucky_draw_clients');
    }
};
