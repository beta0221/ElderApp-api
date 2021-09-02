<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClinicUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clinic_user_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('clinic_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->boolean('is_complete')->default(false);
            $table->dateTime('complete_at')->nullable();
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
        Schema::dropIfExists('clinic_user_logs');
    }
}
