<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('portal_users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique();
            $table->string('name', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('status', 50)->default('ACTIVE');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->json('metadata')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('status', 'idx_portal_users_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('portal_users');
    }
};
