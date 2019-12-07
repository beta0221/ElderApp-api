<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $guarded=[];


    public function products(){
        return $this->belongsToMany('App\Product','product_location','location_id','product_id');
    }
    
}
