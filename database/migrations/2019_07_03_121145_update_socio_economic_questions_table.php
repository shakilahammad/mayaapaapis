<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSocioEconomicQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('socio_economic_questions', function (Blueprint $table) {
            $table->string('ses_question_bn');
            $table->text('ses_answer_bn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('socio_economic_questions', function (Blueprint $table) {
            $table->dropColumn('socio_economic_questions');
            $table->dropColumn('ses_answer_bn');
        });
    }
}
