<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQueuePremiumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_queues', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('inviter_id');
            $table->unsignedInteger('invited_id');
            $table->timestamp('effective_time');
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->timestamps();

            $table->foreign('inviter_id')->references('id')->on('users');
            $table->foreign('invited_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('premium_queues');
    }
}
