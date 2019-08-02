<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string("title")->nullable();
            $table->string("sub-title")->nullable();
            $table->enum("type",["debit","credit"])->default('credit');
            $table->string("message")->nullable();
            $table->integer("point")->default(0);
            $table->string("segment")->nullable();
            $table->string("action_type")->nullable();

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
        Schema::dropIfExists('point_sources');
    }
}
