<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use stdClass;

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
    public static function insert_row($user_id,$order_delievery_id,$location_id,$order_numero,Product $product,$cash_quantity,$point_cash_quantity){
        
        $total_quantity = (int)$cash_quantity + (int)$point_cash_quantity;
        $total_cash = (int)$point_cash_quantity * $product->pay_cash_price + (int)$cash_quantity * $product->cash;
        $total_point = (int)$point_cash_quantity * $product->pay_cash_point;

        
        $ship_status = static::STATUS_READY;
        if($location_id){
            $ship_status = static::STATUS_ARRIVE;
        }

        Order::create([
            'user_id'=>$user_id,
            'order_delievery_id'=>$order_delievery_id,
            'location_id'=>$location_id,
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
            //
            'ship_status'=>$ship_status,
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

    public static function groupOrdersByNumero($orders){
        $_dict = [];
        $orderList = [];
        foreach ($orders as $order) {
            if(!isset($_dict[$order->order_numero])){
                $_order = new stdClass();
                $_order->created_at = $order->created_at;
                $_order->order_numero = $order->order_numero;
                $_order->ship_status = $order->ship_status;
                $_order->user_id = $order->user_id;
                $_order->list = [];
                //
                $orderList[] = $_order;
                $_dict[$order->order_numero] = count($orderList) - 1;
            }
        }
        foreach ($orders as $order) {
            $index = $_dict[$order->order_numero];
            $orderList[$index]->list[] = $order;
        }
        return $orderList;
    }

}
