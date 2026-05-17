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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->after('permissions', function ($table) {
                    $table->date('expire_date', 255)->nullable();
                });

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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'expire_date')) {
                $table->dropColumn('expire_date');
            }

            $table->dropForeign(['created_by']); // Correct way
            $table->dropForeign(['updated_by']); // Correct way

            $table->dropColumn(['created_by', 'updated_by']); // Drop both columns
        });
    }
};
