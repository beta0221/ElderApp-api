<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event_user extends Model
{


    public $primaryKey='id';

    public $timestamps=true;

    public function user(){
        return $this->belongsTo('App\User');
    }
    public function event(){
        return $this->belongsTo('App\Event');
    }
}
