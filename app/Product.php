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
        return $this->belongsToMany('App\Location','product_location','product_id','location_id');
    }
    //relations


    public function getLocationAndQuantity(){
        return DB::table('product_location')->where('product_id',$this->id)->get();
    }

    public function updateQuantity($location_id,$quantity,$payCashQuantity){
        
        try {
            DB::table('product_location')->where('product_id',$this->id)->where('location_id',(int)$location_id)->update([
                'quantity'=>(int)$quantity,
                'pay_cash_quantity'=>(int)$payCashQuantity,
            ]);
        } catch (\Throwable $th) {
            return $th;
        }
        return true;

    }

    public function minusOneQuantity($location_id){
        if($row = DB::table('product_location')->where('product_id',$this->id)->where('location_id',$location_id)->first()){
            if($row->quantity <= 0){
                return false;
            }
            $q = $row->quantity -= 1;
            DB::table('product_location')->where('product_id',$this->id)->where('location_id',$location_id)->update(['quantity'=>$q]);
            return true;
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
            $productImageDict[$product->id] = '/images/products/' . $product->slug . '/' . $product->img;
        }
        return $productImageDict;
    }

    public static function allAvailable(){
        return DB::table('products')->where('public',1)->orderBy('id','desc')->get();
    }

}
