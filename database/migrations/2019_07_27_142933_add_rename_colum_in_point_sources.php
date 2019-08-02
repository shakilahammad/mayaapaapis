<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRenameColumInPointSources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_sources', function (Blueprint $table) {
            DB::statement("ALTER TABLE `point_sources` CHANGE COLUMN `sub_title` `sub_title_en`  varchar(255) DEFAULT 'NULL'");
            DB::statement("ALTER TABLE `point_sources` CHANGE COLUMN `title` `title_en`  varchar(255) DEFAULT 'NULL'");
            $table->string('sub_title_bn')->nullable();
            $table->string('title_bn')->nullable();

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
