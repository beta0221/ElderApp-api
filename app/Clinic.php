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

}
