<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FreqEventUser extends Model
{
    protected $guarded=[];

    protected $table = 'frequently_event_user';

    
    public static function isRewardDrawed($user_id,$day){
        if(DB::table('frequently_event_user')->where('user_id',$user_id)->where('day',$day)->first()){
            return true;
        }

        return false;
    }

    public static function drawReward($user_id,$day,$event_id){

        DB::table('frequently_event_user')->insert([
            'user_id'=>$user_id,
            'event_id'=>$event_id,
            'day'=>$day,
            "created_at" =>  \Carbon\Carbon::now(),
            "updated_at" => \Carbon\Carbon::now(),
        ]);

    }
    

}
