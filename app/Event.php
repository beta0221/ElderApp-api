<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    public $primaryKey='id';

    public $timestamps=true;

    protected $guarded=[];    

    const TYPE_ONETIME = 1;
    const TYPE_FREQUENTLY = 2;
    const MIN_DAYS = 8;
    
    public function certificate(){
        return $this->hasOne('App\EventCertificate','event_id');
    }

    public function guests(){
        return $this->belongsToMany('App\User','event_users','event_id','user_id');
    }
    public function category(){
        return $this->belongsTo('App\Category');
    }

    public function managers(){
        return $this->belongsToMany('App\User','event_manager','event_id','user_id');
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

    public function isArrived($user_id){
        if($row = DB::table('event_users')->where('event_id',$this->id)->where('user_id',$user_id)->first()){
            return $row->arrive;
        }
        return true;
    }

    public function arrive($user_id){
        DB::table('event_users')->where('event_id',$this->id)->where('user_id',$user_id)->update([
            'arrive'=>true
        ]);
    }


    public function drawReward($user_id){
        DB::table('event_users')->where('event_id',$this->id)->where('user_id',$user_id)->update([
            'reward_drawed'=>1,
        ]);
    }

    /**課程進度是否結束 */
    public function isFinished(){
        if($this->days <= $this->current_day){
            return true;
        }
        return false;
    }

    /**查詢有沒有發布紀錄 */
    public function hasIssued(){
        if($row = $this->certificate()->find($this->certificate->id)){
            return true;
        }
        return false;
    }

    /**頒發結業證書 */
    public function issueCertificateToUsers(){
        $users = $this->guests()->get();
        foreach ($users as $user) {
            $user->issueCertificate($this->certificate);
        }
    }

    public static function getDistrictDict($idArray){
        $districts = DB::table('districts')->whereIn('id',$idArray)->get();
        $dict = [];
        foreach ($districts as $district) {
            $dict[$district->id] = $district;
        }
        return $dict;
    }

    public static function getRewardDict(){
        $rewards = DB::table('reward_level')->get();
        $dict = [];
        foreach ($rewards as $reward) {
            $dict[$reward->id] = $reward;
        }
        return $dict;
    }



}
