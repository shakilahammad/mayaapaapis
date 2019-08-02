<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePremiumQuestionQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('premium_question_queue', function (Blueprint $table) {
            $table->enum('status', ['pending', 'answered', 'spam'])->default('pending')->after('limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('premium_question_queue', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
    
}
