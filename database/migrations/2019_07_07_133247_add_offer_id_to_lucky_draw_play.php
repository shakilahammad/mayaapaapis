<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOfferIdToLuckyDrawPlay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lucky_draw_play', function (Blueprint $table) {
            $table->unsignedInteger('offer_id');

            $table->foreign('offer_id')->references('id')->on('lucky_draw_offer_sets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lucky_draw_play', function (Blueprint $table) {
            //
        });
    }
}
