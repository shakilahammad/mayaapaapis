<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiteracyMeasureQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('literacy_measure_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->text('body');
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
        Schema::dropIfExists('literacy_measure_questions');
    }
}
