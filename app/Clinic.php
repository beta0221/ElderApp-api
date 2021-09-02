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

    public function users(){
        return $this->belongsToMany('App\User','clinic_user','clinic_id','user_id');
    }

    public function userLogs(){
        return $this->hasMany('App\ClinicUserLog','clinic_id','id');
    }


    //取得字典
    public static function getDictByIdArray($id_array,$column){
        $clinicList = Clinic::whereIn('id',$id_array)->get();
        $dict = [];
        foreach ($clinicList as $clinic) {
            $dict[$clinic->id] = $clinic->{$column};
        }
        return $dict;
    }

    /**診所 QRcode 字串 */
    public function QRCodeString(){
        $query = "clinic,$this->slug";
        $query = base64_encode($query) . rand(0,9) . rand(0,9);
        return config('app.url') . "?query=$query";
    }


}
