<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_log', function (Blueprint $table) {
            
            $table->bigIncrements('id');

            $table->integer('location_id');
            $table->integer('product_id');

            $table->tinyInteger('give_take');

            $table->integer('quantity_gift')->nullable();
            $table->integer('quantity_cash')->nullable();

            $table->string('comment')->nullable();

            $table->integer('close_gift')->nullable();
            $table->integer('close_cash')->nullable();

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
        Schema::dropIfExists('inventory_log');
    }
}
