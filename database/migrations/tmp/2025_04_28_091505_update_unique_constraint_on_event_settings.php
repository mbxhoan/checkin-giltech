<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('event_settings', function (Blueprint $table) {
            // First, drop the old unique constraint
            $table->dropUnique('event_settings_event_id_name_unique');

            // Then, add the new unique constraint
            $table->unique(['event_id', 'name', 'group']);
        });
    }

    public function down()
    {
        Schema::table('event_settings', function (Blueprint $table) {
            // Rollback: drop the new constraint
            $table->dropUnique('event_settings_event_id_name_group_unique');

            // Restore the old constraint
            $table->unique(['event_id', 'name']);
        });
    }
};
