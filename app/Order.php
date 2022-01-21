<?php

namespace App;

use App\Helpers\InventoryAction;
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
    const STATUS_VOID = 5;

    public static $shipStatusDict = [
        '0'=>'待出貨',
        '1'=>'準備中',
        '2'=>'已出貨',
        '3'=>'已到貨',
        '4'=>'結案',
        '5'=>'作廢',
    ];

    public function user(){
        return $this->hasOne('App\User','id','user_id');
    }

    /**
     * insert 進資料庫
     * * @param Int user_id 
     * @return Order
     */
    public static function insert_row($user_id,$order_delievery_id,$location_id,Product $product,$point_cash_quantity,ProductPackage $package = null,int $bonus_discount = 0){
        
        $order_numero = rand(0,9) . time() . rand(0,9);

        $total_quantity = (int)$point_cash_quantity;
        $total_cash = (int)$point_cash_quantity * $product->pay_cash_price;
        $total_point = (int)$point_cash_quantity * $product->pay_cash_point;

        
        $ship_status = static::STATUS_READY;
        if($location_id){
            $ship_status = static::STATUS_ARRIVE;
        }

        $platform_fee_rate = null;
        $organization_fee_rate = null;
        $host_bonus = null;

        if(!is_null($package)){
            $platform_fee_rate = $package->platform_fee_rate;
            $organization_fee_rate = $package->organization_fee_rate;
            $host_bonus = $package->price * ($package->host_bonus_rate / 100);

            $total_cash = $package->price;
            $total_point = $total_point / 2;
        }

        if($bonus_discount != 0){
            $total_cash -= $bonus_discount;
        }

        $order = Order::create([
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
            'cash_quantity'=>0,
            'total_quantity'=>$total_quantity,
            //
            'platform_fee_rate' => $platform_fee_rate,
            'organization_fee_rate' => $organization_fee_rate,
            'host_bonus' => $host_bonus,
            //
            'total_point'=>$total_point,
            'total_cash'=>$total_cash,
            'bonus_discount'=>$bonus_discount,
            //
            'ship_status'=>$ship_status,
        ]);

        return $order;
    }

    /**訂單進入下階段狀態 */
    public static function updateToNextStatus($firm_id,$order_numero){
        $order = Order::where('order_numero',$order_numero)->where('firm_id',$firm_id)->first();
        if(!$order){
            return 0;
        }
        if($order->ship_status == Order::STATUS_CLOSE || $order->ship_status == Order::STATUS_VOID){
            return -1;
        }
        $nextStatus = $order->ship_status + 1;
        $data = ['ship_status'=>$nextStatus];

        if($nextStatus == Order::STATUS_CLOSE){
            date_default_timezone_set('Asia/Taipei');
            $now = date('Y-m-d h:i:s');
            $data['closed_at'] = $now;

            if($order->host_bonus){
                $order->user()->increaseBonus($order->host_bonus);
            }
        }

        $order->update($data);
        return 1;
    }

    /**作廢訂單 */
    public function voidOrder(){

        //已結案（領取過紅利）
        if($this->ship_status == Order::STATUS_CLOSE && !is_null($this->host_bonus)){
            $this->user()->decreaseBonus($this->host_bonus);
        }

        //使用過紅利折扣
        if($this->bonus_discount != 0){
            $this->user()->increaseBonus($this->bonus_discount);
        }

        $this->ship_status = Order::STATUS_VOID;
        $this->save();
        
        $eventName = "訂單作廢：" . $this->order_numero;
        $this->user->update_wallet_with_trans(User::INCREASE_WALLET,$this->total_point,$eventName);


        if($this->order_delievery_id){
            return;
        }
        $invAction = InventoryAction::getInstance($this->location_id,$this->product_id,Inventory::TARGET_CASH,Inventory::ACTION_ADD,$this->total_quantity,'系統-訂單作廢');
        Inventory::updateInventory($invAction);
    }

    public static function groupOrdersByNumero($orders){
        $_dict = [];
        $orderList = [];
        foreach ($orders as $order) {
            if(!isset($_dict[$order->order_numero])){
                $_order = new stdClass();
                $_order->created_at = $order->created_at->format('Y-m-d');
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
