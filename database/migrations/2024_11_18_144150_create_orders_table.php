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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->nullable();
            // $table->foreignId('ticket_id')
            //     ->nullable(false);
            $table->bigInteger('ref_id')
                ->nullable();
            $table->string("no", 200)
                ->nullable(false);
            $table->string("code", 200)
                ->nullable();
            $table->string("token", 255)
                ->nullable();
            $table->string("payment_url", 255)
                ->nullable();
            $table->decimal('price', 15, 2)
                ->default(0)
                ->nullable(false);
            $table->dateTime("expiry_date")
                ->nullable(false);
            $table->json("ipn")
                ->nullable();
            $table->string("status", 50)
                ->default('NEW')
                ->nullable(false);
            $table->timestamps();

            $table->index('id', 'idx_id');
            $table->index('client_id', 'idx_client_id');
            // $table->index('ticket_id', 'idx_ticket_id');

            $table->foreign('client_id')
                ->references('id')
                ->on('clients')
                ->onDelete('set null')
                ->onUpdate('cascade');
            // $table->foreign('ticket_id')
            //     ->references('id')
            //     ->on('tickets')
            //     ->onDelete('cascade')
            //     ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
