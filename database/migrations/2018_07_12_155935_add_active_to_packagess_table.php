<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveToPackagessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('premium_packages', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('days');
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
            $table->dropColumn(['status']);
        });
    }
}
