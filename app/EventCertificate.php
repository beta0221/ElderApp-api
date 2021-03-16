<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventCertificate extends Model
{
    protected $guarded=[];

    
    public function event(){
        return $this->hasOne('App\Event','id');
    }

    public function getTitle(){
        return '結業獎勵-' . $this->event->title;
    }
    
}
