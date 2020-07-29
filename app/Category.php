<?php

namespace App;

use App\Event;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $primaryKey='id';

    public $timestamps=true;

    protected $guarded=[];    
    
    public function events(){
        return $this->hasMany('App\Event');
    }

    /**類別字典 key是 id */
    public static function getCatDict(){
        $cats = Category::all();
        $dict = [];
        foreach ($cats as $cat) {
            $dict[$cat->id] = $cat;
        }
        return $dict;
    }
}
