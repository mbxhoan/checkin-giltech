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
        Schema::create('lucky_draw_reward_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_id')
                ->constrained('lucky_draw_rewards')
                ->onDelete('cascade');
            $table->foreignId('lucky_draw_client_id')
                ->constrained('lucky_draw_clients')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['reward_id', 'lucky_draw_client_id'], 'uniq_reward_client');
            $table->index('reward_id', 'idx_reward_id');
            $table->index('lucky_draw_client_id', 'idx_lucky_draw_client_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lucky_draw_reward_assignees');
    }
};

