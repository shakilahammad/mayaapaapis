<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodCyclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_cycles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable(false);
            $table->timestamp('last_period_date')->nullable(false);
            $table->integer('period_cycle')->nullable(false);
            $table->integer('period_length')->nullable(false);
            $table->integer('birth_year')->nullable(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('period_cycles');
    }
}
