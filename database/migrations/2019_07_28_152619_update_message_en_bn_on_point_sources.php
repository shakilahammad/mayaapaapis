<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMessageEnBnOnPointSources extends Migration
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
            DB::statement("ALTER TABLE `point_sources` CHANGE COLUMN `message` `message_en`  varchar(255) DEFAULT 'NULL'");
            $table->string("message_bn");

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
