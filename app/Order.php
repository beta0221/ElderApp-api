<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = [];

    public static $shipStatusDict = [
        '0'=>'待出貨',
        '1'=>'準備中',
        '2'=>'已出貨',
        '3'=>'已到貨',
        '4'=>'結案',
    ];

    /**
     * insert 進資料庫
     * * @param Int user_id 
     */
    public static function insert_row($user_id,$order_delievery_id,$order_numero,Product $product,$point_quantity,$point_cash_quantity){
        
        $total_quantity = (int)$point_quantity + (int)$point_cash_quantity;
        $total_cash = (int)$point_cash_quantity * $product->pay_cash_price;
        $total_point = (int)$point_cash_quantity * $product->pay_cash_point + (int)$point_quantity * $product->price;

        Order::create([
            'user_id'=>$user_id,
            'order_delievery_id'=>$order_delievery_id,
            'order_numero'=>$order_numero,
            //
            'firm_id'=>$product->firm_id,
            'product_id'=>$product->id,
            'name'=>$product->name,
            'price'=>$product->price,
            'pay_cash_price'=>$product->pay_cash_price,
            'pay_cash_point'=>$product->pay_cash_point,
            //
            'point_quantity'=>$point_quantity,
            'point_cash_quantity'=>$point_cash_quantity,
            'total_quantity'=>$total_quantity,
            //
            'total_point'=>$total_point,
            'total_cash'=>$total_cash,
        ]);
    }
}
