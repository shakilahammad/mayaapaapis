<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushNotificationMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_notification_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('body');
            $table->string('noti_type', 10);
            $table->string('action_type', 10);
            $table->string('class_type', 10);
            $table->string('class_name', 10);
            $table->string('promo_code', 10);
            $table->string('url');
            $table->string('image_url');
            $table->string('header_text', 10);
            $table->string('details_text', 10);
            $table->string('btn_text', 10);
            $table->string('log_in_needed', 3);
            $table->bigInteger('question_id');
            $table->string('noti_task', 20);
            $table->string('action_data', 20);
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
        Schema::dropIfExists('push_notification_messages');
    }
}
