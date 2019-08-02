<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodTipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_tips', function (Blueprint $table) {
            $table->increments('id');
            $table->text('tips_en')->nullable();
            $table->text('tips_bn')->nullable();
            $table->enum('day_type', ['period_day', 'safe_day', 'risk_day']);
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
        Schema::dropIfExists('period_tips');
    }
}
