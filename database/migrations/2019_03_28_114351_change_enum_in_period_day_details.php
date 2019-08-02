<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEnumInPeriodDayDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE period_days_details CHANGE COLUMN day_type day_type ENUM('period_day','pregnancy_high_day','unsafe_day','unknown') NOT NULL DEFAULT 'period_day'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('period_day_details', function (Blueprint $table) {
            //
        });
    }
}
