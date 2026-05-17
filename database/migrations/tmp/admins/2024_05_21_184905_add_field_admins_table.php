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
            $table->after('name', function ($table) {
                $table->string('username', 200)
                    ->nullable(false);
            });

            $table->after('remember_token', function ($table) {
                $table->string('license_token', 255)
                    ->nullable();
            });

            $table->string('session_id')
                ->nullable()
                ->after('last_login_at');
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
            $table->dropColumn('username');
            $table->dropColumn('license_token');
            $table->dropColumn('session_id');
        });
    }
};
