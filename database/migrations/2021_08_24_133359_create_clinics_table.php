<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClinicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('slug',50);
            $table->string('name',50);
            $table->string('image',50)->nullable();
            $table->string('address');
            $table->string('link')->nullable();
            $table->date('session_date')->nullable();
            $table->integer('session')->nullable();
            $table->mediumText('description')->nullable();

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
        Schema::dropIfExists('clinics');
    }
}
