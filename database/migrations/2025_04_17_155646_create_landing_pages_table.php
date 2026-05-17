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
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('template_id', 50)
                ->default(1)
                ->nullable(false);
            $table->foreignId('event_id')
                ->nullable(false);
            $table->boolean('show_language_selection')
                ->default(false)
                ->nullable(false);
            $table->string('slug', 200)
                ->unique()
                ->nullable(false);
            $table->json('trackings')
                ->nullable();
            $table->json('customs')
                ->nullable();
            $table->json('orders')
                ->nullable();
            $table->string('align', 20)
                ->default('center')
                ->nullable(false);
            // $table->string('form_class')
            //     ->default()
            //     ->nullable();
            $table->string('form_width')
                ->default()
                ->nullable();
            $table->string('font')
                ->nullable();
            $table->json('languages')
                ->nullable();
            $table->integer('banner_id')
                ->unsigned()
                ->nullable();
            $table->integer('header_id')
                ->unsigned()
                ->nullable();
            $table->integer('footer_id')
                ->unsigned()
                ->nullable();
            $table->integer('bg_desktop_id')
                ->unsigned()
                ->nullable();
            $table->integer('bg_tablet_id')
                ->unsigned()
                ->nullable();
            $table->integer('bg_mobile_id')
                ->unsigned()
                ->nullable();
            $table->string('contact_name', 255)
                ->nullable();
            $table->string('contact_phone', 255)
                ->nullable();
            $table->string('contact_email', 255)
                ->nullable();
            $table->string('contact_address', 255)
                ->nullable();
            $table->string('status', 50)
                ->default('NEW')
                ->nullable(false);
            $table->integer('created_by')
                ->unsigned()
                ->nullable();
            $table->integer('updated_by')
                ->unsigned()
                ->nullable();
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('event_id', 'idx_event_id');
            $table->unique(['event_id', 'slug']);
            $table->index('created_by', 'idx_created_by');
            $table->index('updated_by', 'idx_updated_by');

            /* Make foregin key */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
            $table->foreign('banner_id')
                ->references('id')
                ->on('media');
            $table->foreign('header_id')
                ->references('id')
                ->on('media');
            $table->foreign('footer_id')
                ->references('id')
                ->on('media');
            $table->foreign('bg_desktop_id')
                ->references('id')
                ->on('media');
            $table->foreign('bg_tablet_id')
                ->references('id')
                ->on('media');
            $table->foreign('bg_mobile_id')
                ->references('id')
                ->on('media');
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
        Schema::dropIfExists('landing_pages');
    }
};
