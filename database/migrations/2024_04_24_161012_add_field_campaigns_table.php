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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->after('from_name', function ($table) {
                $table->json('cc')
                    ->nullable();
                $table->json('bcc')
                    ->nullable();
            });

            $table->after('from_name', function ($table) {
                $table->boolean('fixed_attachments')
                    ->default(true)
                    ->nullable(false);
            });

            $table->after('status', function ($table) {
                $table->integer('created_by')->unsigned()->nullable();
                $table->integer('updated_by')->unsigned()->nullable();
            });

            /* Make index */
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');

            /* Make foregin key */
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
            $table->foreign('updated_by')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('cc');
            $table->dropColumn('bcc');
            $table->dropColumn('fixed_attachments');

            $table->dropForeign('campaigns_created_by_foreign');
            $table->dropColumn('created_by');
            // $table->dropIndex('idx_created_by');
            $table->dropForeign('campaigns_updated_by_foreign');
            $table->dropColumn('updated_by');
            // $table->dropIndex('idx_updated_by');
        });
    }
};
