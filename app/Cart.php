<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * 取得ip的購物車所有產品
     * @param String ip
     * @return Product 產品陣列
     */
    public static function getProductsInCart($ip){
        if(!$cart = Cart::where('ip',$ip)->first()){
            return null;
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
