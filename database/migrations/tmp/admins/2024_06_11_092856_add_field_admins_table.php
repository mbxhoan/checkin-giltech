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
            $table->after('id', function ($table) {
                $table->string('company_code', 200)
                    ->nullable();
            });

            $table->after('license_token', function ($table) {
                $table->boolean('active_license')
                    ->default(false)
                    ->nullable(false);
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
            $table->dropColumn('company_code');
            $table->dropColumn('active_license');
        });
    }
};
