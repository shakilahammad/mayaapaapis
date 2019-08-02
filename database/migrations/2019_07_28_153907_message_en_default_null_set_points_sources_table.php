<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MessageEnDefaultNullSetPointsSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_sources', function (Blueprint $table) {
            //
            DB::statement("ALTER TABLE `point_sources` MODIFY COLUMN `message_bn` varchar(255) DEFAULT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_sources', function (Blueprint $table) {
            //
        });
    }
}
