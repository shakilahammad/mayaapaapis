<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePointSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_sources', function (Blueprint $table) {
            DB::statement("ALTER TABLE `point_sources` CHANGE COLUMN `sub-title` `sub_title`  varchar(255) DEFAULT 'NULL'");
        });
    }

    /**clea
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
