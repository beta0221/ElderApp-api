<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_packages', function (Blueprint $table) {


            $table->bigIncrements('id');

            $table->integer('product_id')->unsigned();
            //數量
            $table->integer('quantity')->unsigned();
            //單價
            $table->integer('price_per_item')->unsigned();
            //團購價
            $table->integer('price')->unsigned();
            //平台手續費
            $table->integer('platform_fee_rate')->unsigned();
            //協會回饋
            $table->integer('organization_fee_rate')->unsigned();
            //團媽回饋
            $table->integer('host_bonus_rate')->unsigned();
            //市價
            $table->integer('market_price')->unsigned();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_packages');
    }
}
