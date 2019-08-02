<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('premium_packages', function (Blueprint $table) {
            $table->text('condition_en')->nullable()->after('question_cap');
            $table->text('condition_bn')->nullable()->after('condition_en');
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
            $table->dropColumn(['condition_en', 'condition_bn']);
        });
    }
}
