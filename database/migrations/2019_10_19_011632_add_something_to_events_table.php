<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSomethingToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('dateTime_2')->after('dateTime')->nullable();
            $table->integer('people')->after('maximum')->default(0);
            $table->integer('reward_level_id')->after('district_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('dateTime_2');
            $table->dropColumn('people');
            $table->dropColumn('reward_level_id');
        });
    }
}
