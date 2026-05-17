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
        Schema::create('custom_field_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable(false);
            $table->boolean('is_default')
                ->default(false)
                ->nullable(false);
            $table->boolean('is_show')
                ->default(false)
                ->nullable(false);
            $table->boolean('is_lp')
                ->default(false)
                ->nullable(false);
            $table->boolean('is_checkin_mobile')
                ->default(false)
                ->nullable(false);
            $table->boolean('is_checkin_desktop')
                ->default(false)
                ->nullable(false);
            $table->boolean('show_prefix')
                ->default(false)
                ->nullable(false);
            $table->boolean('required')
                ->default(false)
                ->nullable(false);
            $table->boolean('unique')
                ->default(false)
                ->nullable(false);
            $table->string('name')
                ->nullable(false);
            $table->string('description')
                ->nullable();
            $table->string('placeholder')
                ->nullable();
            $table->string('icon')
                ->nullable();
            $table->integer('order')
                ->nullable(false);
            $table->string('type')
                ->default('TEXT')
                ->nullable(false)
                ->comment('TEXT NUMBER PHONE EMAIL CHECKBOX MULTICHOICE SELECT RADIO AVATAR HIDDEN');
                /* TEXT NUMBER PHONE EMAIL CHECKBOX MULTICHOICE SELECT RADIO AVATAR HIDDEN */
            $table->json('accepts')
                ->nullable();
            $table->json('options')
                ->nullable();
            $table->json('checkins')
                ->nullable();
            $table->json('landing_page')
                ->nullable();
            $table->timestamps();

            $table->index('event_id', 'idx_event_id');
            $table->unique(['event_id', 'name']);

            /* Make foregin key */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_templates');
    }
};
