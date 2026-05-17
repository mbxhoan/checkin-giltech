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
        Schema::table('labels', function (Blueprint $table) {
            $table->after('status', function ($table) {
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();

                $table->foreign('created_by')
                    ->references('id')
                    ->on('users');
                $table->foreign('updated_by')
                    ->references('id')
                    ->on('users');
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
        Schema::table('labels', function (Blueprint $table) {
            $table->dropForeign(['created_by']); // Correct way
            $table->dropForeign(['updated_by']); // Correct way

            $table->dropColumn(['created_by', 'updated_by']); // Drop both columns
        });
    }
};
