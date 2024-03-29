<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();

            $table->string('name',50);
            $table->string('identity_number',50);
            $table->string('phone',50);
            $table->date('birthdate');

            $table->string('occupation',50);
            $table->string('relation',50);
            $table->boolean('q_1');
            $table->boolean('q_2');
            $table->boolean('q_3');
            $table->boolean('q_4');
            $table->boolean('q_5');
            $table->string('description')->nullable();

            $table->enum('status',['pending','processing','verified','close','void'])->default('pending');
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
        Schema::dropIfExists('insurances');
    }
}
