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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->nullable(false);
            $table->string('event_code', 200)
                ->nullable();
            $table->bigInteger('ref_id')
                ->nullable();
            $table->bigInteger('lp_id')
                ->nullable();
            $table->string('qrcode', 200)
                ->nullable(false);
            $table->string('name', 255)
                ->nullable(false);
            $table->string('email', 255)
                ->nullable();
            $table->json('custom_fields')
                ->nullable();
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('name', 'idx_name');
            $table->index('email', 'idx_email');
            $table->index('event_id', 'idx_event_id');
            $table->index('event_code', 'idx_event_code');
            $table->index('qrcode', 'idx_qrcode');
            // $table->unique('qrcode', 'idx_qrcode_unique');

            $table->unique(['event_id', 'qrcode']);

            /* Make foregin key */
            $table->foreign('event_id')
                ->references('id')
                ->on('events');
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
};
