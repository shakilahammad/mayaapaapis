<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonalizedAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personalized_ads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ad_name');
            $table->string('img_url');
            $table->string('url');
            $table->unsignedInteger('click')->default(0);
            $table->string('header_text');
            $table->string('detail_text');
            $table->string('ad_type');
            $table->string('priority');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personalized_ads');
    }
}
