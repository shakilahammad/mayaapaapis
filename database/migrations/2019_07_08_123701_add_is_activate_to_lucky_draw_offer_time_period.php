<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActivateToLuckyDrawOfferTimePeriod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lucky_draw_offer_time_period', function (Blueprint $table) {
            $table->boolean('is_activate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lucky_draw_offer_time_period', function (Blueprint $table) {
            //
        });
    }
}
