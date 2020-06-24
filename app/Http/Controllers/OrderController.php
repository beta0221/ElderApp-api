<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDelievery;
use Illuminate\Http\Request;

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

        if(!$orderDelievery = OrderDelievery::find($orders[0]->order_delievery_id)){
            return view('errors.404');
        }

        return view('order.detail',[
            'order_numero'=>$order_numero,
            'orders'=>$orders,
            'orderDelievery'=>$orderDelievery
        ]);

    }
    public function view_orderList(){
        if(!$orders=Order::where('user_id',2)->get()){
            return view('errors.404');
        }
        return view('order.list',[
            'orders'=>$orders
        ]);
    }
    
}
