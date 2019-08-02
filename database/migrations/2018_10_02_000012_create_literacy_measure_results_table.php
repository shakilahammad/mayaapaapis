<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiteracyMeasureResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('literacy_measure_results', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('mcq_id');
            $table->unsignedInteger('question_id');
            $table->string('answer')->comment("0 = Not Sure, 1 = Yes, 2 = No");
            $table->enum('type', ['pre', 'post'])->default('pre');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('mcq_id')->references('id')->on('literacy_measure_mcq');
            $table->foreign('question_id')->references('id')->on('questions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('literacy_measure_results');
    }
}
