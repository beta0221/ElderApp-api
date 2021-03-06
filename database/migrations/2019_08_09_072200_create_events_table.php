<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('public')->default(0);
            $table->integer('category_id');
            $table->integer('district_id');
            $table->integer('owner_id')->nullable();
            $table->integer('reward_level_id')->default(1);
            $table->string('slug');
            $table->string('title');
            $table->mediumText('body')->nullable();
            $table->dateTime('dateTime')->nullable();
            $table->dateTime('dateTime_2')->nullable();
            $table->string('location');
            $table->string('image')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->integer('maximum')->default(20);
            $table->integer('people')->default(0);
            

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
        Schema::dropIfExists('events');
    }
}
