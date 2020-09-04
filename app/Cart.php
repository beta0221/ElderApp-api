<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart';
    protected $guarded = [];
    public $timestamps = false;

    /**運費 */
    const SHIPPING_FEE = 100;

    /**計算運費 */
    public static function cacuShippingFee($productList){
        $firms = [];
        foreach ($productList as $product) {
            $firms[] = $product->firm_id;
        }
        $firmCount = count(array_count_values($firms));
        $shipping_fee = $firmCount * self::SHIPPING_FEE;
        return $shipping_fee;
    }

    public static function addToCart($product_id){
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
        return $cart;
    }

    /**
     * 取得ip的購物車所有產品
     * @param String ip
     * @return Product 產品陣列
     */
    public static function getProductsInCart($ip){
        if(!$cart = Cart::where('ip',$ip)->first()){
            return [];
        }
        $items = json_decode($cart->items,true);
        
        $products = Product::whereIn('id',$items)->get();
        return $products;
    }

    /**
     * 清除ip的購物車所有產品
     * @param String ip
     * @return Void
     */
    public static function clearCart($ip){
        if($cart = Cart::where('ip',$ip)->first()){
            $cart->items = json_encode([]);
            $cart->save();
        }
    }


}
