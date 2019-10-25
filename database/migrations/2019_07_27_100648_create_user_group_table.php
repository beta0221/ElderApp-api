<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id');
            $table->integer('user_id');
            $table->integer('level');
            $table->integer('lv_1')->nullable();
            $table->integer('lv_2')->nullable();
            $table->integer('lv_3')->nullable();
            $table->integer('lv_4')->nullable();
            $table->integer('lv_5')->nullable();
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
        Schema::dropIfExists('user_group');
    }
}
