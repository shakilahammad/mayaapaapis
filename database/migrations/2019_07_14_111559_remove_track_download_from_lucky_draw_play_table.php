<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveTrackDownloadFromLuckyDrawPlayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lucky_draw_play', function (Blueprint $table) {
            $table->dropForeign(['track_download_id']);
            $table->dropColumn('track_download_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lucky_draw_play', function (Blueprint $table) {
            //
        });
    }
}
