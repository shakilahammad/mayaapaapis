<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLuckyDrawPlay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lucky_draw_play', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('track_download_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('track_download_id')->references('id')->on('track_download');
            $table->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('LuckyDrawPlay');
    }
}
