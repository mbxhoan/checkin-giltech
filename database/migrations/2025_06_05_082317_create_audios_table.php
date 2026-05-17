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
        Schema::create('audios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->nullable();
            $table->string('code', 200)
                ->unique()
                ->nullable(false);
            $table->string('text', 100)
                ->nullable(false);
            $table->string('voice')
                ->default('alloy');
            $table->string('file_path')
                ->nullable();
            $table->string('link')
                ->nullable();
            $table->integer('created_by')
                ->unsigned()
                ->nullable();
            $table->integer('updated_by')
                ->unsigned()
                ->nullable();
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('code', 'idx_code');
            $table->index('company_id', 'idx_company_id');
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');

            $table->foreign('company_id')
                ->references('id')
                ->on('companys');
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
     */
    public function down(): void
    {
        Schema::dropIfExists('audios');
    }
};
