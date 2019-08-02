<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePremiumPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('gateway')->nullable(); //Bank
            $table->string('gateway_category', 10)->nullable(); //Bank
            $table->string('gateway_network', 10)->nullable(); //Bank
            $table->string('currency', 5)->default('BDT'); //Currency
            $table->string('provider', 25)->default('portwallet'); //Portwallet
            $table->string('issuer', 50)->nullable(); //Original Bank of user
            $table->unsignedInteger('package_id'); //Premium Package
            $table->unsignedInteger('user_id'); //User ID
            $table->string('invoice_id', 25); //Payment Invoice ID
            $table->integer('question_count')->default(0); //Number of questions asked during package
            $table->decimal('amount', 5, 2); //Package amount
            $table->boolean('is_refunded')->default(false); //Paid or Refunded
            $table->enum('status', ['pending','active', 'expired', 'refunded', 'free_premium'])->default('active'); //User Premium Status
            $table->dateTime('refunded_at')->default('0000-00-00 00:00:00'); //Refunded Time
            $table->dateTime('effective_time'); //Package Activation Time
            $table->dateTime('expiry_time'); //Package Activation Time
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('package_id')->references('id')->on('premium_packages');
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
        Schema::dropIfExists('premium_payments');
    }
}
