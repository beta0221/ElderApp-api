<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->integer('event_type')->default(1)->after('slug');
            $table->integer('days')->nullable()->after('people');
            $table->integer('require_signup')->default(1)->after('days');
            $table->integer('price')->nullable()->after('require_signup');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('require_signup');
            $table->dropColumn('days');
            $table->dropColumn('event_type');
        });
    }
}
