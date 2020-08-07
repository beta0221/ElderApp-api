<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Order;
use App\User;
use App\OrderDelievery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{

    public function __construct()
    {
        $this->middleware('webAuth', ['only' => ['index','checkOut']]);
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

        $user = User::web_user();
        $wallet_remain = $user->wallet;

        return view('cart.cart',[
            'products'=>$products,
            'wallet_remain'=>$wallet_remain,
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
            Session::flash('message','系統錯誤');
            return redirect()->route('cart_page');
        }

        $total_point = 0;   //總共要花多少樂幣
        foreach ($products as $product) {
            if(!isset($quantityDict[$product->id])){ continue; }
            // $point_quantity = (isset($quantityDict[$product->id]['point']))?(int)$quantityDict[$product->id]['point']:0;
            $point_cash_quantity = (isset($quantityDict[$product->id]['point_cash']))?(int)$quantityDict[$product->id]['point_cash']:0;
            $total_point += (int)$point_cash_quantity * $product->pay_cash_point;
        }

        if($user->wallet < $total_point){
            Session::flash('message','樂幣不足');
            return redirect()->route('cart_page');
        }

        foreach ($products as $product) {
            if(!isset($quantityDict[$product->id])){ continue; }
            $cash_quantity = (isset($quantityDict[$product->id]['cash']))?(int)$quantityDict[$product->id]['cash']:0;
            $point_cash_quantity = (isset($quantityDict[$product->id]['point_cash']))?(int)$quantityDict[$product->id]['point_cash']:0;
            if(($cash_quantity + $point_cash_quantity) <= 0){ continue; }
            Order::insert_row($user->id,$order_delievery_id,$order_numero,$product,$cash_quantity,$point_cash_quantity);
        }

        $user->update_wallet_with_trans(User::DECREASE_WALLET,$total_point,"訂單：$order_numero");
        Cart::clearCart($ip);

        return redirect('/order/thankyou/'.$order_numero);

    }

}
