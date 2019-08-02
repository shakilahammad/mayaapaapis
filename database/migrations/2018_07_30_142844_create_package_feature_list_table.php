<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePackageFeatureListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_feature_list', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('p2')->default(false);
            $table->boolean('p3')->default(false);;
            $table->boolean('p4')->default(false);;
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('premium_feature_list');
    }
}
