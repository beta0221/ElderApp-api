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
     * locationDict = 
     * {
     *    'product_id':'location_id',
     *    '30':22,
     * }
     * 
     */
    public function checkOut(Request $request){
        
        $this->validate($request,['delivery_type'=>'required']);

        $validate_rules = ['locationDict'=>'required'];
        if($request->delivery_type == '1'){
            $validate_rules = [
                'quantityDict'=>'required',
                'receiver_name'=>'required',
                'receiver_phone'=>'required',
                'county'=>'required',
                'district'=>'required',
                'zipcode'=>'required',
                'address'=>'required',
            ];
        }
        $this->validate($request,$validate_rules);

        $ip = request()->ip();
        
        $user = User::web_user();
        $products = Cart::getProductsInCart($ip);
        
        $quantityDict = json_decode($request->quantityDict,true);
        $locationDict = json_decode($request->locationDict,true);

        if($request->delivery_type == '1'){
            Session::flash('message','目前暫不提供宅配服務');
            return redirect()->route('cart_page');
            // if(!$order_delievery_id = OrderDelievery::insert_row($user->id,$request)){
            //     Session::flash('message','系統錯誤');
            //     return redirect()->route('cart_page');
            // }
        }else{
            foreach ($products as $product) {
                if(!isset($locationDict[$product->id])){
                    Session::flash('message','請選擇據點');
                    return redirect()->route('cart_page');
                }
            }
        }   

        $total_point = 0;   //總共要花多少樂幣
        $order_numero_point_dict = [];
        foreach ($products as $product) {
            if(!isset($quantityDict[$product->id])){ continue; }
            if(!isset($order_numero_point_dict[$product->firm_id])){ $order_numero_point_dict[$product->firm_id] = 0; }

            // $point_quantity = (isset($quantityDict[$product->id]['point']))?(int)$quantityDict[$product->id]['point']:0;
            $point_cash_quantity = (isset($quantityDict[$product->id]['point_cash']))?(int)$quantityDict[$product->id]['point_cash']:0;
            
            //計算庫存夠不夠
            $location_id = $locationDict[$product->id];
            $location = $product->locations()->find($location_id);
            $stock = $location->pivot->quantity_cash;
            if($point_cash_quantity > $stock){
                Session::flash('message','目前 "' . $product->name . '" 在 "' . $location->name . '" 據點的庫存數量剩餘:' . $stock);
                return redirect()->route('cart_page');
            }

            $point = (int)$point_cash_quantity * $product->pay_cash_point;
            $order_numero_point_dict[$product->firm_id] += $point;
            $total_point += $point;
        }

        if($user->wallet < $total_point){
            Session::flash('message','樂幣不足');
            return redirect()->route('cart_page');
        }
        
        
        $order_numero_dict = [];
        foreach ($products as $product) {
            if(!isset($quantityDict[$product->id])){ continue; }
            //$cash_quantity = (isset($quantityDict[$product->id]['cash']))?(int)$quantityDict[$product->id]['cash']:0;
            $cash_quantity = 0;
            $point_cash_quantity = (isset($quantityDict[$product->id]['point_cash']))?(int)$quantityDict[$product->id]['point_cash']:0;
            if(($cash_quantity + $point_cash_quantity) <= 0){ continue; }

            if(!isset($order_numero_dict[$product->firm_id])){
                $order_numero_dict[$product->firm_id] = rand(0,9) . time() . rand(0,9);
            }
            $order_numero = $order_numero_dict[$product->firm_id];
            
            if($request->delivery_type == '1'){ //宅配
                // Order::insert_row($user->id,$order_delievery_id,null,$order_numero,$product,$cash_quantity,$point_cash_quantity);
            }else{  //據點
                if(!isset($locationDict[$product->id])){ continue; }
                $location_id = $locationDict[$product->id];
                Order::insert_row($user->id,null,$location_id,$order_numero,$product,$cash_quantity,$point_cash_quantity);

                $invAction = InventoryAction::getInstance($location_id,$product->id,Inventory::TARGET_CASH,Inventory::ACTION_REMOVE,$point_cash_quantity,'系統-商城購買商品');
                Inventory::updateInventory($invAction);

            }
        }

        $order_numero_array = [];
        foreach ($order_numero_dict as $firm_id => $order_numero) {
            $order_numero_array[] = $order_numero;
            $point = $order_numero_point_dict[$firm_id];
            $user->update_wallet_with_trans(User::DECREASE_WALLET,$point,"訂單：$order_numero");    
        }
        
        Cart::clearCart($ip);
        $order_numero_array = implode(',',$order_numero_array);
        
        return redirect('/order/thankyou/'.$order_numero_array);

    }

}
