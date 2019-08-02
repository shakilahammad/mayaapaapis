<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePremiumQueuestionQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_queuestion_queue', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('question_id');
            $table->enum('limit', ['ten', 'thirty', 'ninty', 'others'])->default('others');
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('questions');
            $table->index('question_id');
            $table->index('limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('premium_queuestion_queue');
    }
}
