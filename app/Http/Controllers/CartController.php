<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Helpers\InventoryAction;
use App\Inventory;
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
        $shipping_fee = Cart::cacuShippingFee($products);

        $user = User::web_user();
        $wallet_remain = $user->wallet;
        $static_host = config('app.static_host');

        $locationDict = [];
        foreach ($products as $product) {
            $locationDict[$product->id] = $product->locations()->get();
        }

        return view('cart.cart',[
            'products'=>$products,
            'wallet_remain'=>$wallet_remain,
            'locationDict'=>$locationDict,
            'static_host'=>$static_host,
            'shipping_fee'=>$shipping_fee
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
        
        $cart = Cart::addToCart($product_id);

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


}
