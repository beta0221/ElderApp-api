<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $table = 'products';
    public $timestamps=false;
    protected $guarded=[];


    public function getRouteKeyName()
    {
        return 'slug';
    }

    //relations
    public function category(){
        return $this->belongsTo('App\ProductCategory','product_category_id','id');
    }

    public function locations(){
        return $this->belongsToMany('App\Location','product_location','product_id','location_id')->withPivot(['quantity','quantity_cash']);
    }
    //relations


    public function getLocationAndQuantity(){
        return DB::table('product_location')->where('product_id',$this->id)->get();
    }

    public function getQuantity($location_id){
        return DB::table('product_location')->where('product_id',$this->id)->where('location_id',$location_id)->first();
    }

    public function updateQuantity($location_id,$quantity){
        DB::table('product_location')->where('product_id',$this->id)->where('location_id',(int)$location_id)->update([
            'quantity'=>(int)$quantity,
        ]);
    }

    public function updateQuantityCash($location_id,$quantity_cash){
        DB::table('product_location')->where('product_id',$this->id)->where('location_id',(int)$location_id)->update([
            'quantity_cash'=>(int)$quantity_cash,
        ]);
    }

    /**庫存是否足夠 */
    public function isAvailable($location_id,$quantity,$target){
        if($row = DB::table('product_location')->where('product_id',$this->id)->where('location_id',$location_id)->first()){
            
            $inv_quantity = 0;
            switch ($target) {
                case Inventory::TARGET_GIFT:
                    $inv_quantity = $row->quantity;
                    break;
                case Inventory::TARGET_CASH:
                    $inv_quantity = $row->quantity_cash;
                    break;
                default:
                    break;
            }
            if($inv_quantity >= $quantity){ return true; }
        }

        return false;
    }

    /** 使用者已經換取的商品總數*/
    public function hasExchangedSumBy($user_id){
        return OrderDetail::where('product_id',$this->id)->where('user_id',$user_id)->count();
    }

    /** abandoned */
    public static function getProductDictionary_abandoned(){

        $products = DB::table('products')->select(['id','name','slug','img'])->get();
        $productDictionary=[];
        foreach ($products as $row) {
            $p = [];
            $p['name'] = $row->name;
            $slug = $row->slug;
            $img = $row->img;
            $p['img'] = "/images/products/$slug/$img";
            $productDictionary[$row->id] = $p;
        }
        return $productDictionary;
    }

    public static function getProductDict($idArray){
        $products = Product::whereIn('id',$idArray)->get();
        $dict = [];
        foreach ($products as $product) {
            $dict[$product->id] = $product;
        }
        return $dict;
    }

    

    /**
     * 取得產品的圖片url字典
     * @param Array product id 陣列
     * @return Array 字典 key:product_id value:圖片 url
     */
    public static function getProductImageDict($productIdArray){
        $productImageDict = [];
        $products = Product::whereIn('id',$productIdArray)->get();
        foreach ($products as $product) {
            $productImageDict[$product->id] = config('app.static_host') . "/products/$product->slug/$product->img";
        }
        return $productImageDict;
    }


}
