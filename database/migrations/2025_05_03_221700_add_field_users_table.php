<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->after('event_id', function ($table) {
                $table->foreignId('package_id')
                    ->nullable();

                $table->index('package_id', 'idx_package_id');

                $table->foreign('package_id')
                    ->references('id')
                    ->on('packages');
            });

            $table->after('remember_token', function ($table) {
                $table->string('verify_token', 200)
                    ->nullable()
                    ->unique();
                $table->string('session_id', 200)
                    ->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['package_id']); // Correct way
            $table->dropIndex('idx_package_id');
            $table->dropColumn(['package_id']); // Drop both columns
            $table->dropColumn('verify_token');
            $table->dropColumn('session_id');
        });
    }
};
