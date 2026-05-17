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
        Schema::create('historys', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')
                ->nullable(true);
            $table->string('action', 255)
                ->nullable(false);
            $table->string('object', 255)
                ->nullable();
            $table->string('function', 50)
                ->nullable();
            $table->string('method', 50)
                ->nullable();
            $table->json('parameters')
                ->nullable();
            $table->string('error', 255)
                ->nullable();
            $table->timestamps();

            $table->index('user_id', 'idx_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historys');
    }
};
