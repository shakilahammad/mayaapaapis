<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPointSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lucky_draw_offer_sets', function (Blueprint $table) {
            $table->unsignedInteger("point_source")->nullable();

            //

            $table->foreign('point_source')->references('id')->on('point_sources');
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
