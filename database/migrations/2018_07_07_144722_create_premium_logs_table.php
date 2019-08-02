<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePremiumLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id'); //User ID
            $table->enum('status', ['request', 'pending', 'cancelled', 'expired', 'accepted', 'refund_pending', 'refund_processing', 'refunded', 'partially_refunded', 'charged_back', 'rejected', 'invalid_request', 'not_allowed'])->default('pending'); //Payment Request Status
            $table->text('data'); //request & response
            $table->text('user_agent'); //JSON response
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('premium_logs');
    }
}
