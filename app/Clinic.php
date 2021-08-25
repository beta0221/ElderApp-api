<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $guarded=[];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function managers(){
        return $this->belongsToMany('App\User','clinic_manager','clinic_id','user_id');
    }


}
