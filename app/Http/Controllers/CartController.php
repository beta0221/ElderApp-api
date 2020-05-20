<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Product;
use Illuminate\Http\Request;

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
        if(!$cart = Cart::where('ip',$ip)->first()){
            return response('no items in cart');
        }
        $items = json_decode($cart->items,true);

        $products = Product::whereIn('id',$items)->get();


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
}
