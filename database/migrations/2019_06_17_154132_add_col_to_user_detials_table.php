<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColToUserDetialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_detials', function (Blueprint $table) {
            $table->string('member_id')->after('id')->nullable();
            $table->string('id_number')->after('img')->nullable();

            $table->string('district_id')->after('id_number')->nullable();
            $table->string('district_name')->after('district_id')->nullable();
            

            $table->string('inviter')->after('district_name')->nullable();
            $table->string('inviter_phone')->after('inviter')->nullable();
            $table->string('emg_contact')->after('inviter_phone')->nullable();
            $table->string('emg_phone')->after('emg_contact')->nullable();
            $table->integer('org_rank')->after('emg_phone')->nullable();
            $table->integer('pay_status')->after('org_rank')->default(0);
            $table->date('join_date')->after('pay_status')->nullable();
            $table->date('last_pay_date')->after('join_date')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_detials', function (Blueprint $table) {
            $table->dropColumn(['member_id','id_number','inviter','inviter_phone','emg_contact','emg_phone','org_rank','pay_status','join_date','last_pay_date']);
        });
    }
}
