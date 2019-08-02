<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointBadgeCriterion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_badge_criterion', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("badge_id")->default(0);
            $table->unsignedInteger("source_id")->default(0);
            $table->unsignedInteger("num_of_transaction")->default(0);
            $table->timestamps();
            $table->foreign('badge_id')->references('id')->on('point_badges');
            $table->foreign('source_id')->references('id')->on('point_sources');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('point_badge_criterion');
    }
}
