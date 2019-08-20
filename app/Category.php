<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $primaryKey='id';

    public $timestamps=true;

    protected $guarded=[];    
    
    public function events(){
        return $this->hasMany(Event::class);
    }
}
