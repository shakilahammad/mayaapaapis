<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePremiumPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('premium_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_en', 50);
            $table->string('name_bn', 50);
            $table->text('desc_en');
            $table->text('desc_bn');
            $table->decimal('price', 5, 2);
            $table->smallInteger('days');
            $table->integer('question_cap');
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
        Schema::dropIfExists('premium_packages');
    }
}
