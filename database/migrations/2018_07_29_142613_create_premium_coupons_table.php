<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePremiumCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 50);
            $table->integer('discount');
            $table->integer('max_discount');
            $table->enum('type', ['promo', 'invite'])->default('promo');
            $table->timestamp('expiry_time');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('premium_coupon_applied', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('coupon_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('coupon_id')->references('id')->on('premium_coupons');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('premium_coupons');
        Schema::dropIfExists('premium_coupon_applied');
    }
}
