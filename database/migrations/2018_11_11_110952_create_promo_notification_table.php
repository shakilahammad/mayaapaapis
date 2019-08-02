<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromoNotificationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications_promo', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('notifications_message_id');
            $table->string('title_en', 200);
            $table->string('title_bn', 200);
            $table->text('detail');
            $table->integer('count');
            $table->enum('type', ['promo'])->default('promo');
            $table->string('action_data', 15);
            $table->string('class_name', 50);
            $table->dateTime('expiry_time');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('notifications_message_id')->references('id')->on('notifications_messages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications_promo');
    }
}
