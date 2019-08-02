<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LuckyDrawOfferSetColumnChange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lucky_draw_offer_sets', function (Blueprint $table) {
            $table->renameColumn('offer_name', 'offer_name_en');
            $table->string("offer_name_bn");

            //

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
