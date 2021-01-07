<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    protected $guarded=[];


    public function products(){
        return $this->belongsToMany('App\Product','product_location','location_id','product_id')->withPivot(['quantity','quantity_cash']);
    }

    /** abandoned */
    public static function getLocationDictionary_abandoned(){

        $locations = DB::table('locations')->select(['id','name','address'])->get();
        $locationDictionary=[];
        foreach ($locations as $row) {
            $l = [];
            $l['address'] = $row->address;
            $l['name'] = $row->name;
            $locationDictionary[$row->id] = $l;
        }
        return $locationDictionary;
    }

    public static function getLocationDict($idArray){
        $locations = Location::whereIn('id',$idArray)->get();
        $dict = [];
        foreach ($locations as $location) {
            $dict[$location->id] = $location;
        }
        return $dict;
    }

    public function managers(){
        return $this->belongsToMany('App\User','location_manager','location_id','user_id');
    }

    public function getProductQuantityDict(){
        $rows = DB::table('product_location')->where('location_id',$this->id)->get();
        $dict = [];
        foreach ($rows as $row) {
            $dict[$row->product_id] = [
                'quantity'=>$row->quantity,
                'quantity_cash'=>$row->quantity_cash,
            ];
        }
        return $dict;
    }
    
}
