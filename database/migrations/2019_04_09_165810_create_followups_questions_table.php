<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFollowupsQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('followup_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id');
            $table->integer('followup_id');
            $table->timestamps();

//            $table->foreign('followup_questions_question_id_foreign')->references('id')->on('questions');
//            $table->foreign('followup_questions_followup_id_foreign')->references('id')->on('follow_ups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('followups_questions');
    }
}
