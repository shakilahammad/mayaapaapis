<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodCycleMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_cycle_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable(false);
            $table->double('weight')->nullable(false);
            $table->double('sleep')->nullable(false);
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
        Schema::dropIfExists('period_cycle_metas');
    }
}
