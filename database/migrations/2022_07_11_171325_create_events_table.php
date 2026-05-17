<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->nullable(false);
            $table->string('code', 200)
                ->nullable(false);
            $table->string('name', 255)
                ->nullable(false);
            $table->text('description')
                ->nullable();
            $table->string('place')
                ->nullable();
            $table->date('run_date')
                ->nullable();
            $table->json('features')
                ->nullable();
            $table->json('languages')
                ->default(new Expression('(JSON_ARRAY())'));
            $table->tinyInteger('pda_reg')
                ->nullable();
            $table->string('pc_bg', 255)
                ->nullable();
            $table->string('pda_bg', 255)
                ->nullable();
            $table->string('luckydraw_bg', 255)
                ->nullable();
            $table->string('contact_person', 255)
                ->nullable();
            $table->string('contact_phone', 255)
                ->nullable();
            $table->string('contact_email', 255)
                ->nullable();
            $table->json('client_custom_fields')
                ->nullable();
            $table->text('note')
                ->nullable();
            $table->string('status', 50)
                ->default('ACTIVE')
                ->nullable(false);
            $table->timestamps();

            /* Make index */
            $table->index('id', 'idx_id');
            $table->index('code', 'idx_code_unique');
            $table->index('company_id', 'idx_company_id');

            /* Make foregin key */
            $table->foreign('company_id')
                ->references('id')
                ->on('companys');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
};
