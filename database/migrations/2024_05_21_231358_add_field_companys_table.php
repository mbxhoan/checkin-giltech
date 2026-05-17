<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('companys', function (Blueprint $table) {
            // Add the new column without the unique constraint
            $table->string('code')->nullable()->after('id');
        });

        // Ensure there are no duplicate values in the new column
        DB::statement("UPDATE companys SET code = CONCAT('prefix_', id) WHERE code IS NULL");

        // Now make the column unique
        Schema::table('companys', function (Blueprint $table) {
            $table->string('code')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companys', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
