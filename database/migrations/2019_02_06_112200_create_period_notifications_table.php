<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeriodNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('period_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable(false);
            $table->unsignedInteger('notifiable')->nullable(false);
            $table->unsignedInteger('question_id')->nullable();
            $table->unsignedInteger('article_id')->nullable();
            $table->timestamps();

            $table->foreign('notifiable')->references('id')->on('users');
            $table->foreign('question_id')->references('id')->on('questions');

        });

        Schema::table('period_notifications', function($table) {
            $table->foreign('article_id')->references('id')->on('articles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('period_notifications');
    }
}
