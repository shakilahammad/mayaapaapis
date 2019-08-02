<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuizWebUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quiz_web_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('quiz_group_id');
            $table->boolean('is_complete')->default(false);
            $table->integer('result');
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
        Schema::dropIfExists('quiz_web_users');
    }
}
