<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDelievery;
use App\Product;
use Illuminate\Http\Request;
use stdClass;

class OrderController extends Controller
{
    public function view_thankyou($order_numero){
        return view('order.thankyou',[
            'order_numero'=>$order_numero
        ]);
    }

    public function view_orderDetail($order_numero){
        
        if(!$orders = Order::where('order_numero',$order_numero)->get()){
            return view('errors.404');
        }

        $productIdArray = [];
        foreach ($orders as $order) {
            $productIdArray[] = $order->product_id;
        }
        $productImageDict = Product::getProductImageDict($productIdArray);

        if(!$orderDelievery = OrderDelievery::find($orders[0]->order_delievery_id)){
            return view('errors.404');
        }

        return view('order.detail',[
            'shipStatusDict'=>Order::$shipStatusDict,
            'productImageDict'=>$productImageDict,
            'order_numero'=>$order_numero,
            'orders'=>$orders,
            'orderDelievery'=>$orderDelievery
        ]);

    }
    public function view_orderList(){
        if(!$orders=Order::where('user_id',2)->get()){
            return view('errors.404');
        }

        $productIdArray = [];
        foreach ($orders as $order) {
            $productIdArray[] = $order->product_id;
        }
        $productImageDict = Product::getProductImageDict($productIdArray);

        $_dict = [];
        $orderList = [];
        foreach ($orders as $index => $order) {
            if(!isset($_dict[$order->order_numero])){
                $_order = new stdClass();
                $_order->created_at = $order->created_at;
                $_order->order_numero = $order->order_numero;
                $_order->list = [];
                $orderList[] = $_order;
                $_dict[$order->order_numero] = $index;
            }
        }
        foreach ($orders as $order) {
            $index = $_dict[$order->order_numero];
            $orderList[$index]->list[] = $order;
        }
        
        return view('order.list',[
            'productImageDict'=>$productImageDict,
            'orderList'=>$orderList,
            'orders'=>$orders
        ]);
    }
    
}
