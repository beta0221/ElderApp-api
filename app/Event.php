<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    public $primaryKey='id';

    public $timestamps=true;

    protected $guarded=[];    
    
    public function guests(){
        return $this->belongsToMany('App\User','event_users','event_id','user_id');
    }
    public function category(){
        return $this->belongsTo('App\Category');
    }

    public function numberOfPeople(){
        return DB::table('event_users')->where('event_id',$this->id)->count();
    }

    public function isParticipated($user_id){
        $result = DB::table('event_users')->where('event_id',$this->id)->where('user_id',$user_id)->first();
        if($result){
            return true;
        }
        return false;
    }

    public function peopleIncrease(){
        
        $this->people += 1;
        $this->save();
    }
    public function peopleDecrease(){
        $this->people -= 1;
        $this->save();
    }

    public function rewardAmount(){
        $reward_level = DB::table('reward_level')->where('id',$this->reward_level_id)->first();
        return $reward_level->reward;
    }

    public function isRewardDrawed($user_id){
        if($row = DB::table('event_users')->where('event_id',$this->id)->where('user_id',$user_id)->first()){
            return $row->reward_drawed;
        }
        return true;
    }

    public function drawReward($user_id){
        DB::table('event_users')->where('user_id',$user_id)->update([
            'reward_drawed'=>1,
        ]);
    }



}
