<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodUsersModesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_users_modes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('day');
            $table->string('day_type');
            $table->unsignedInteger('mode_id');
            $table->timestamps();

            $table->foreign('mode_id')->references('id')->on('period_mode_lists');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('period_users_modes');
    }
}
