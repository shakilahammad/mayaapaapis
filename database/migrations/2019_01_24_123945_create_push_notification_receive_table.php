<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushNotificationReceiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_notification_receives', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string("title");
            $table->string("body");
            $table->string("noti_type");
            $table->string("action_type");
            $table->string("class_type");
            $table->string("class_name");
            $table->string("promo_code");
            $table->string("url");
            $table->string("image_url");
            $table->string("header_text");
            $table->string("details_text");
            $table->string("btn_text");
            $table->string("log_in_needed");
            $table->unsignedInteger("question_id");
            $table->string("noti_task");
            $table->string("action_data");
            $table->timestamps();

            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("question_id")->references("id")->on("questions");

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('push_notification_receives');
    }
}
