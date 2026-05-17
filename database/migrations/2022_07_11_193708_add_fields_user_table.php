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
                $table->after('id', function ($table) {
                    $table->foreignId('company_id')
                        ->nullable();

                    /* Make index */
                    $table->index('id', 'idx_company_id');

                    /* Make foregin key */
                    $table->foreign('company_id')
                        ->references('id')
                        ->on('companys')
                        ->onDelete('set null');
                });

                $table->after('name', function ($table) {
                    $table->string('username', 255)
                        ->nullable(false);
                    $table->unique('username', 'idx_username_unique');
                });

                $table->after('remember_token', function ($table) {
                    $table->json('permissions')
                        ->nullable();

                    $table->enum('type', ['WEB', 'SCANNER', 'API'])
                        ->nullable(false)
                        ->default('WEB');

                    $table->enum('status', ['NEW', 'INACTIVE', 'ACTIVE', 'SUSPENDED', 'DELETED'])
                        ->nullable(false)
                        ->default('INACTIVE');
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
            $table->dropForeign(['company_id']); // Correct way
            $table->dropColumn(['company_id']); // Drop both columns
            $table->dropIndex('idx_company_id'); // Drop both columns

            if (Schema::hasColumn('users', 'votes')) {
                $table->dropColumn('votes');
            }
        });
    }
};
