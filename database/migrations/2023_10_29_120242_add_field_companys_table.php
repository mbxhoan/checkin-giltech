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
        if (Schema::hasTable('companys')) {
            Schema::table('companys', function (Blueprint $table) {
                $table->after('name', function ($table) {
                    $table->integer('limited_events')
                        ->nullable();
                    $table->integer('limited_clients')
                        ->nullable();
                    $table->integer('limited_emails')
                        ->nullable();
                    $table->integer('limited_users')
                        ->nullable();
                    $table->integer('limited_campaigns')
                        ->nullable();
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
        Schema::table('companys', function (Blueprint $table) {
            if (Schema::hasColumn('companys', 'limited_events')) {
                $table->dropColumn('limited_events');
            }

            if (Schema::hasColumn('companys', 'limited_clients')) {
                $table->dropColumn('limited_clients');
            }

            if (Schema::hasColumn('companys', 'limited_emails')) {
                $table->dropColumn('limited_emails');
            }

            if (Schema::hasColumn('companys', 'limited_users')) {
                $table->dropColumn('limited_users');
            }

            if (Schema::hasColumn('companys', 'limited_campaigns')) {
                $table->dropColumn('limited_campaigns');
            }

            $table->dropForeign(['created_by']); // Correct way
            $table->dropForeign(['updated_by']); // Correct way

            $table->dropColumn(['created_by', 'updated_by']); // Drop both columns
        });
    }
};
