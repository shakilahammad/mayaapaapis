<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GiveawayTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('giveaway_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('user_id');
            $table->integer('source_id');
            $table->integer('value');
            $table->timestamps();
            $table->softDeletes();

//            $table->foreign('product_id')->references('id')->on('giveaway_products');
//            $table->foreign('user_id')->references('id')->on('users');
//            $table->foreign('source_id')->references('id')->on('giveaway_ticket_sources');

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
