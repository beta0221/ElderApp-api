<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->string('id_code')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->integer('wallet')->default(0);
            $table->integer('rank')->default(1);
            

            $table->boolean('gender')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('phone')->nullable();
            $table->string('tel')->nullable();
            $table->string('address')->nullable();

            $table->string('img')->nullable();
            $table->string('id_number')->nullable();

            $table->string('district_id')->nullable();
            $table->string('district_name')->nullable();
            
            $table->integer('inviter_id')->nullable();
            $table->string('inviter')->nullable();
            $table->string('inviter_phone')->nullable();
            $table->string('emg_contact')->nullable();
            $table->string('emg_phone')->nullable();
            $table->integer('org_rank')->nullable();
            $table->integer('role')->nullable();
            $table->integer('pay_status')->default(0);
            $table->integer('pay_method')->nullable();
            $table->date('join_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('valid')->default(false);
            $table->string('invoice')->nullable();

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
        Schema::dropIfExists('users');
    }
}
