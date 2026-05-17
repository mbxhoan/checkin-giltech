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
        Schema::table('webhook_postmarks', function (Blueprint $table) {
            $table->unsignedBigInteger('email_id')->nullable()->after('id');
            // No foreign key constraint since email record could be deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_postmarks', function (Blueprint $table) {
            $table->dropColumn('email_id');
        });
    }
};
