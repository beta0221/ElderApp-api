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

    public static function getProductDictionary(){

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

    public static function allAvailable(){
        return DB::table('products')->where('public',1)->orderBy('id','desc')->get();
    }

}
