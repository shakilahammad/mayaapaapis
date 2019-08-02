<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePremiumPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('premium_payments', function (Blueprint $table) {
            $table->unsignedInteger('coupon_id')->nullable()->after('package_id');

            $table->foreign('coupon_id')->references('id')->on('premium_coupons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('premium_payments', function (Blueprint $table) {
            $table->dropForeign('premium_payments_coupon_id_foreign');
            $table->dropColumn('coupon_id');
        });
    }
}
