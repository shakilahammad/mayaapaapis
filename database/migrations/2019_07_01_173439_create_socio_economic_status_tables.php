<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSocioEconomicStatusTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('socio_economic_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ses_question_en');
            $table->text('ses_answer_en');
            $table->enum('ses_type', ['education', 'location', 'siblings', 'job', 'income']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('socio_economic_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->text('ses_user_answer');
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('socio_economic_questions');
        Schema::dropIfExists('socio_economic_users');
    }
}
