<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->string('actor_type', 100)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 100);
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('level', 20)->default('info');
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->index('event_id', 'idx_audit_logs_event_id');
            $table->index(['actor_type', 'actor_id'], 'idx_audit_logs_actor');
            $table->index(['subject_type', 'subject_id'], 'idx_audit_logs_subject');
            $table->index('action', 'idx_audit_logs_action');
            $table->index('level', 'idx_audit_logs_level');
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
