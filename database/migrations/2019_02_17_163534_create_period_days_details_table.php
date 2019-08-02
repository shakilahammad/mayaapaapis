<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodDaysDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_days_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('day');
            $table->string('pregnancy_possibility');
            $table->string('mental_health_1');
            $table->string('mental_health_2');
            $table->string('physical_health_1');
            $table->string('physical_health_2');
            $table->string('img_mental_health_1');
            $table->string('img_mental_health_2');
            $table->string('img_physical_health_1');
            $table->string('img_physical_health_2');
            $table->text('tips');
            $table->enum('language', ['en', 'bn']);
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
        Schema::dropIfExists('period_days_details');
    }
}
