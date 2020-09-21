<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFirmDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('firm_detail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('firm_id');
            $table->integer('shipping_fee');
            $table->integer('free_shipping_obstacle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('firm_detail');
    }
}
