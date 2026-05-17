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
        Schema::create('event_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('client_types')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->after('package_id', function ($table) {
                $table->foreignId('area_id')->nullable();
                $table->index('area_id', 'idx_area_id');
                $table->foreign('area_id')
                    ->references('id')
                    ->on('event_areas');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_areas');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['area_id']); // Correct way
            $table->dropIndex('idx_area_id');
            $table->dropColumn(['area_id']); // Drop both columns
        });
    }
};
