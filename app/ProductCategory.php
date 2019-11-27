<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'product_category';

    public function products(){
        return $this->hasMany('App\Product','product_category_id','id');
    }
}
