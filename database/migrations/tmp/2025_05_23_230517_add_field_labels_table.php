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
        Schema::table('labels', function (Blueprint $table) {
            $table->after('id', function ($table) {
                $table->foreignId('event_id')
                    ->nullable(false);
                    
                $table->index('event_id', 'idx_event_id');

                /* FOREIGN */
                $table->foreign('event_id')
                    ->references('id')
                    ->on('events');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            //
        });
    }
};
