<?php
namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Tracker{

    public static function log(Request $request){
        
        $user_id = 0;
        if($user = $request->user()){
            $user_id = $user->id;
        }

        date_default_timezone_set("Asia/Taipei");
        $now = date("Y-m-d h:i:sa");
        $url = $request->url();
        $ip = $request->ip();
        
        Log::channel('track')->info("TRACKING AT $now");
        Log::channel('track')->info("url : $url");
        Log::channel('track')->info("ip : $ip");
        Log::channel('track')->info("user id : $user_id");
        Log::channel('track')->info($request->getContent());
        Log::channel('track')->info("-");
        
    }


    public static function info(String $string){
        Log::channel('track')->info("** INFO **");
        Log::channel('track')->info($string);
    }

}