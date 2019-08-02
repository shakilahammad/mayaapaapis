<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuckyDrawOfferSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lucky_draw_offer_sets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('set_id');
            $table->string('offer_name');
            $table->string('offer_type');
            $table->unsignedInteger('discount_percent');
            $table->unsignedInteger('amount_flexi');
            $table->unsignedInteger('tips_id');
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
        Schema::dropIfExists('LuckyDrawOfferSets');
    }
}
