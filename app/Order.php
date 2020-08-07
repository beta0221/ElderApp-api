<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $guarded = [];

    const STATUS_READY = 0;
    const STATUS_PREPARE = 1;
    const STATUS_SHIPPING = 2;
    const STATUS_ARRIVE = 3;
    const STATUS_CLOSE = 4;

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
    public static function insert_row($user_id,$order_delievery_id,$order_numero,Product $product,$cash_quantity,$point_cash_quantity){
        
        $total_quantity = (int)$cash_quantity + (int)$point_cash_quantity;
        $total_cash = (int)$point_cash_quantity * $product->pay_cash_price + (int)$cash_quantity * $product->cash;
        $total_point = (int)$point_cash_quantity * $product->pay_cash_point;

        Order::create([
            'user_id'=>$user_id,
            'order_delievery_id'=>$order_delievery_id,
            'order_numero'=>$order_numero,
            //
            'firm_id'=>$product->firm_id,
            'product_id'=>$product->id,
            'name'=>$product->name,
            'price'=>$product->price,
            'cash'=>$product->cash,
            'pay_cash_price'=>$product->pay_cash_price,
            'pay_cash_point'=>$product->pay_cash_point,
            //
            'point_quantity'=>0,
            'point_cash_quantity'=>$point_cash_quantity,
            'cash_quantity'=>$cash_quantity,
            'total_quantity'=>$total_quantity,
            //
            'total_point'=>$total_point,
            'total_cash'=>$total_cash,
        ]);
    }

    public static function updateToNextStatus($firm_id,$order_numero){
        $first = Order::where('order_numero',$order_numero)->where('firm_id',$firm_id)->first();
        if(!$first){
            return 0;
        }
        if($first->ship_status == Order::STATUS_CLOSE){
            return -1;
        }
        $nextStatus = $first->ship_status + 1;
        Order::where('order_numero',$order_numero)->where('firm_id',$firm_id)->update([
            'ship_status'=>$nextStatus
        ]);
        return 1;
    }
}
