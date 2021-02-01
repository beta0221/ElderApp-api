<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UploadProductInfoImageLog extends Model
{
    protected $guarded = [];

    public static function log($slug,$imageUrl,$imagePath){
        static::create([
            'slug'=>$slug,
            'imageUrl'=>$imageUrl,
            'imagePath'=>$imagePath,
        ]);
    }
}
