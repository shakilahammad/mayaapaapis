<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePushNotificiationReceive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table("push_notification_receives", function ($table){
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->string("title")->nullable()->change();
            $table->string("body")->nullable()->change();
            $table->string("noti_type")->nullable()->change();
            $table->string("action_type")->nullable()->change();
            $table->string("class_type")->nullable()->change();
            $table->string("class_name")->nullable()->change();
            $table->string("promo_code")->nullable()->change();
            $table->string("url")->nullable()->change();
            $table->string("image_url")->nullable()->change();
            $table->string("header_text")->nullable()->change();
            $table->string("details_text")->nullable()->change();
            $table->string("btn_text")->nullable()->change();
            $table->string("log_in_needed")->nullable()->change();
            $table->unsignedInteger("question_id")->nullable()->change();
            $table->string("noti_task")->nullable()->change();
            $table->string("action_data")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
