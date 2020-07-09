<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Order;
use App\User;
use App\OrderDelievery;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    public function __construct()
    {
        $this->middleware('webAuth', ['only' => ['checkOut']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ip = request()->ip();
        $products = Cart::getProductsInCart($ip);

        return view('cart.cart',[
            'products'=>$products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$product_id)
    {
        
        $ip = request()->ip();
        
        $cart = Cart::firstOrCreate(['ip'=>$ip]);

        $items = json_decode($cart->items,true);
        if($items){
            if(!in_array($product_id,$items)){
                array_push($items,$product_id);
            }
        }else{
            $items = [$product_id];
        }
        
        $cart->items = json_encode($items);
        $cart->save();

        return response()->json($cart);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($product_id)
    {
        $ip = request()->ip();

        if(!$cart = Cart::where('ip',$ip)->first()){
            return response(['s'=>0,'m'=>'no item in cart']);
        }

        if($items = json_decode($cart->items,true)){
            $_items = [];
            foreach ($items as $i) {
                if($i == $product_id){
                    continue;
                }
                $_items[] = $i;
            }
            $cart->items = json_encode($_items);
            $cart->save();
        }
        
        return response([
            's'=>1,
            'm'=>'success'
        ]);

    }

    /**
     * 結帳
     * 
     * quantityDict = 
     * {
     *    '123':{
     *          'point':3,
     *          'point_cash':6,
     *        }
     *  }
     * 
     */
    public function checkOut(Request $request){

        $this->validate($request,[
            'quantityDict'=>'required',
            'receiver_name'=>'required',
            'receiver_phone'=>'required',
            'county'=>'required',
            'district'=>'required',
            'zipcode'=>'required',
            'address'=>'required',
        ]);

        $ip = request()->ip();
        $order_numero = rand(0,9) . time() . rand(0,9);
        $user = User::web_user();
        $products = Cart::getProductsInCart($ip);
        $quantityDict = json_decode($request->quantityDict,true);

        if(!$order_delievery_id = OrderDelievery::insert_row($user->id,$request)){
            return redirect()->route('cart_page');
        }

        $total_point = 0;   //總共要花多少樂幣
        foreach ($products as $product) {
            if(!isset($quantityDict[$product->id])){ continue; }
            $point_quantity = (isset($quantityDict[$product->id]['point']))?(int)$quantityDict[$product->id]['point']:0;
            $point_cash_quantity = (isset($quantityDict[$product->id]['point_cash']))?(int)$quantityDict[$product->id]['point_cash']:0;
            $total_point += (int)$point_cash_quantity * $product->pay_cash_point + (int)$point_quantity * $product->price;
        }

        if($user->wallet < $total_point){
            return redirect()->route('cart_page');
        }

        foreach ($products as $product) {
            if(!isset($quantityDict[$product->id])){ continue; }
            $point_quantity = (isset($quantityDict[$product->id]['point']))?(int)$quantityDict[$product->id]['point']:0;
            $point_cash_quantity = (isset($quantityDict[$product->id]['point_cash']))?(int)$quantityDict[$product->id]['point_cash']:0;
            if(($point_quantity + $point_cash_quantity) <= 0){ continue; }
            Order::insert_row($user->id,$order_delievery_id,$order_numero,$product,$point_quantity,$point_cash_quantity);
        }

        Cart::clearCart($ip);

        return redirect('/order/thankyou/'.$order_numero);

    }

}
