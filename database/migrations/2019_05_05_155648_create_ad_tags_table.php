<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('personalized_ad_id')->nullable();
            $table->unsignedInteger('tag_id')->nullable();
            $table->timestamps();

            $table->foreign('personalized_ad_id')->references('id')->on('personalized_ads');
            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_tags');
    }
}
