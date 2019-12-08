<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Location extends Model
{
    protected $guarded=[];


    public function products(){
        return $this->belongsToMany('App\Product','product_location','location_id','product_id');
    }

    public static function getLocationDictionary(){

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
    
}
