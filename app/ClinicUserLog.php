<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClinicUserLog extends Model
{
    protected $guarded=[];

    public function clinic(){
        return $this->belongsTo('App\Clinic');
    }


}
