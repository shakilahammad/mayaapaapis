<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveIsActivateFromLuckyDrawOfferSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lucky_draw_offer_sets', function (Blueprint $table) {
            $table->dropColumn('is_activate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lucky_draw_offer_sets', function (Blueprint $table) {
            //
        });
    }
}
