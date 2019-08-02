<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSeenToFollowUpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->decimal('is_seen', 1, 0)->after('notify_at')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropColumn('is_seen');
        });
    }
}
