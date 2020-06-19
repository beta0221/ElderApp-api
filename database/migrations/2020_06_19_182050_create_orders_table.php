<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('user_id');
            $table->integer('firm_id');
            $table->integer('order_delievery_id');
            $table->string('order_numero');

            $table->string('name');
            $table->integer('price');
            $table->integer('pay_cash_price');
            $table->integer('pay_cash_point');

            $table->integer('point_quantity');
            $table->integer('point_cash_quantity');
            $table->integer('total_quantity');

            $table->integer('total_point');
            $table->integer('total_cash');

            $table->integer('pay_status')->default(0);
            $table->integer('ship_status')->default(0);

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
        Schema::dropIfExists('orders');
    }
}
