<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuckyDrawOfferTimePeriod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lucky_draw_offer_time_period', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('set_id');
            $table->unsignedInteger('days_length');
            $table->timestamp('start_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('LuckyDrawOfferTimePeriod');
    }
}
