<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePeriodMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('period_cycle_metas', function (Blueprint $table){
           $table->dropColumn('weight');
           $table->unsignedInteger('period_cycle_id');

           $table->foreign('period_cycle_id')->references('id')->on('period_cycles');
        });

        Schema::rename('period_cycle_metas', 'period_cycle_meta_sleeps');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
