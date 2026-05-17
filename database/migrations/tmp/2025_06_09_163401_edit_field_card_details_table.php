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
        Schema::table('card_details', function (Blueprint $table) {
            $table->decimal('pos_x', 8, 4)
                ->default(10)
                ->nullable(false)
                ->change();
            $table->decimal('pos_y', 8, 4)
                ->default(10)
                ->nullable(false)
                ->change();
            $table->decimal('size', 8, 4)
                ->default(300)
                ->nullable()
                ->change();
            $table->decimal('font_size', 8, 4)
                ->default(50)
                ->nullable()
                ->change();
            $table->string('font', 50)
                ->comment('font path')
                ->default('svn-arial/SVN-Arial-Bold.ttf')
                ->nullable()
                ->change();
            $table->decimal('width', 8, 4)
                ->default(300)
                ->nullable()
                ->change();
            $table->decimal('height', 8, 4)
                ->default(300)
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_details', function (Blueprint $table) {
            //
        });
    }
};
