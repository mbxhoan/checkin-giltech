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
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('event_code');
            $table->json('metadata')
                ->nullable()
                ->after('dates_valid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'metadata')) {
                $table->dropColumn('metadata');
            }

            if (Schema::hasColumn('tickets', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
