<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("user_id")->default(0);
            $table->unsignedInteger("source_id")->default(0);
            $table->unsignedInteger("parent_id")->default(0);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('source_id')->references('id')->on('point_sources');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('point_transactions');
    }
}
