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
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('permissions');
            $table->dropColumn('menu_permissions');

            $table->after('limited_menus', function ($table) {
                $table->json('allow_features')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->after('limited_menus', function ($table) {
                $table->json('permissions')->nullable();
                $table->json('menu_permissions')->nullable();
            });

            $table->dropColumn('allow_features');
        });
    }
};
