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
        if (Schema::hasTable('export_datas')) {
            Schema::table('export_datas', function (Blueprint $table) {
                $table->after('file_path', function ($table) {
                    $table->string('file_name', 255)->nullable();
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
        Schema::table('export_datas', function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
};
