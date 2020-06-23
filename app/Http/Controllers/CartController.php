<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Order;
use App\OrderDelievery;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
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
            return response('no items in cart');
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
        
        return response()->json($cart);

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
        $user = Auth::user();
        $products = Cart::getProductsInCart($ip);
        
        

        if(!$order_delievery_id = OrderDelievery::insert_row($user->id,$request)){
            return response('失敗',500);
        }


        foreach ($products as $product) {
            
            $point_quantity = $request->quantityDict[$product->id]['point'];
            $point_cash_quantity = $request->quantityDict[$product->id]['point_cash'];
            
            Order::insert_row($user->id,$order_delievery_id,$order_numero,$product,$point_quantity,$point_cash_quantity);

        }
        


        Cart::clearCart($ip);

        return response($products);

    }

}
