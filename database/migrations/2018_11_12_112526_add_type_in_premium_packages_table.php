<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeInPremiumPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('premium_packages', function (Blueprint $table) {
            $table->enum('type', ['normal', 'prescription'])->default('normal')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('premium_packages', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });
    }
}
