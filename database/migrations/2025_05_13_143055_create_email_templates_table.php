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
        Schema::dropIfExists('email_templates');

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('ref_id', 200)
                ->nullable();
            $table->string('uuid', 200)
                ->nullable()
                ->unique();
            $table->string('name', 255)
                ->nullable(false);
            $table->string('subject', 255)
                ->nullable();
            $table->string('banner', 255)
                ->nullable();
            $table->string('footer', 255)
                ->nullable();
            $table->json('texts')
                ->nullable();
            $table->longText('html')
                ->nullable();
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable();
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('ref_id', 'idx_ref_id');
            $table->index('uuid', 'idx_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
