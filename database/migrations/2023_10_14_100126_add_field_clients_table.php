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
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->after('event_id', function ($table) {
                    $table->foreignId('country_id')->nullable()->after('event_id');
                    $table->index('country_id', 'idx_country_id');
                    $table->foreign('country_id')->references('id')->on('countrys');
                });
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropIndex('idx_country_id');
            $table->dropColumn('country_id');
        });
    }
};
