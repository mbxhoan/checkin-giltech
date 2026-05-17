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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255);
            $table->timestamp('email_verified_at')
                ->nullable();
            $table->string('password', 255);
            $table->json('permissions')
                ->nullable();
            $table->json('menu_permissions')
                ->nullable();
            $table->boolean('supper_user')
                ->default(false)
                ->nullable(false);
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->rememberToken();
            $table->dateTime('last_login_at')
                ->nullable();
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('email', 'idx_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admins');
    }
};
