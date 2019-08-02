<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePremiumIpnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_ipns', function (Blueprint $table) {
            $table->increments('id');
            $table->string('currency', 5)->default('BDT'); //Currency
            $table->string('lang', 5)->default('eng'); //Currency
            $table->string('username', 25)->nullable(); //Currency
            $table->decimal('amount', 5, 2); //Package amount
            $table->integer('invoice_id'); //Payment Invoice ID
            $table->unsignedInteger('user_id'); //User ID
            $table->unsignedInteger('payment_id'); //User ID
            $table->enum('status', ['request', 'pending', 'cancelled', 'expired', 'accepted', 'refund_pending', 'refund_processing', 'refunded', 'partially_refunded', 'charged_back', 'rejected', 'invalid_request', 'not_allowed'])->default('pending'); //Payment Request Status
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('payment_id')->references('id')->on('premium_payments');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('premium_ipns');
    }
}
