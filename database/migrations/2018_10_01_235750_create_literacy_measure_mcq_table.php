<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiteracyMeasureMcqTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('literacy_measure_mcq', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question');
            $table->boolean('correct_answer')->default(false);
            $table->unsignedInteger('tag_id');
            $table->enum('type', ['pre', 'post'])->default('pre');
            $table->timestamps();

            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('literacy_measure_mcq');
    }
}
