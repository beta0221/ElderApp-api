<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExchangeMaxToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('cash')->default(0)->after('pay_cash_point');
            $table->integer('exchange_max')->nullable()->after('img');
            $table->integer('purchase_max')->nullable()->after('exchange_max');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cash');
            $table->dropColumn('exchange_max');
            $table->dropColumn('purchase_max');
        });
    }
}
