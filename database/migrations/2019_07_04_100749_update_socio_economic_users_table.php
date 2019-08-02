<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSocioEconomicUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('socio_economic_users', function (Blueprint $table) {
            $table->tinyInteger('seu_siblings')->before('is_complete');
            $table->tinyInteger('seu_income')->before('is_complete');
            $table->tinyInteger('seu_job')->before('is_complete');
            $table->tinyInteger('seu_location')->before('is_complete');
            $table->tinyInteger('seu_education')->before('is_complete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('socio_economic_users', function (Blueprint $table) {
            $table->dropColumn('seu_siblings');
            $table->dropColumn('seu_income');
            $table->dropColumn('seu_job');
            $table->dropColumn('seu_location');
            $table->dropColumn('seu_education');
        });
    }
}
