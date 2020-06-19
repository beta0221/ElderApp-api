<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart';
    protected $guarded = [];
    public $timestamps = false;


    public static function getProductsInCart($ip){
        if(!$cart = Cart::where('ip',$ip)->first()){
            return null;
        }
        $items = json_decode($cart->items,true);
        
        $products = Product::whereIn('id',$items)->get();
        return $products;
    }


}
