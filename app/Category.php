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
}
