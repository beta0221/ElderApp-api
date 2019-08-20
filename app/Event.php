<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public $primaryKey='id';

    public $timestamps=true;

    protected $guarded=[];    
    
    public function guests(){
        return $this->belongsToMany('App\User','event_users','event_id','user_id');
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
