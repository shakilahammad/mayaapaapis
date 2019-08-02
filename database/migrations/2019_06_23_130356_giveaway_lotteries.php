<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GiveawayLotteries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('giveaway_lotteries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->integer('product_id');
            $table->timestamps();
            $table->softDeletes();

//            $table->foreign('product_id')->references('id')->on('giveaway_products');
//            $table->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
