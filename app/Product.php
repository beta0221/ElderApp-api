<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    public $timestamps=false;
    protected $guarded=[];

    protected static function boot()
    {
        parent::boot();

        static::creating(function($product){
            $product->slug = 'P'.time();
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function category(){
        return $this->belongsTo('App\ProductCategory','product_category_id','id');
    }
}
