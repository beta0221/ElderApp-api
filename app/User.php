<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable implements JWTSubject
{

    const INCREASE_WALLET = true;
    const DECREASE_WALLET = false;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_code',
        'name',
        'email',
        'password',
        'gender',
        'birthdate',
        'phone',
        'tel',
        'address',
        'img',
        'id_number',
        'district_id',
        'district_name',
        'inviter_id',
        'inviter',
        'inviter_phone',
        'pay_status',
        'pay_method',
        'expiry_date',
        'valid',
        'invoice',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public static function web_user(){
        $user = JWTAuth::parseToken()->authenticate();
        return $user;
    }



    //database relationship binding

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function isAdmin()
    {
        $roles = $this->roles()->get();
        foreach ($roles as $role) {   
            if ($role->name=='admin') {
                return true;
            }    
        }
        return false;
    }

    public function removeMemberFromGroup(){
        $user_level = $this->groupLevel();
        $lv = 'lv_' . $user_level;
        try {
            if($user_level > 1){
                DB::table('user_group')->where($lv,$this->id)->update([
                    $lv=>null
                ]);
            }
            DB::table('user_group')->where('user_id',$this->id)->delete();
        } catch (\Throwable $th) {
            Log::info($th);
            return false;
        }
        return true;
        
    }

    public function joinToGroup($leader_id,$level){

        if($row = DB::table('user_group')->where('user_id',$leader_id)->first()){

            $insert = [
                'group_id'=>$row->group_id,
                'user_id'=>$this->id,
                'level'=>$level,
            ];

            for($i=1;$i<=5;$i++){
                if($i == $level){
                    $insert["lv_$i"] = $this->id;
                }else if($i == $row->level){
                    $insert["lv_$i"] = $row->user_id;
                }else{
                    $str = "lv_$i";
                    $insert["lv_$i"] = $row->$str;
                }
            }
            try {
                DB::table('user_group')->insert($insert);
                $this->org_rank = $level;
                $this->save();
            } catch (\Throwable $th) {
                return false;
                //log here $th
            }

            return true;
        }

        return false;
    }

    public function mergeToGroup($leader_id){

        if(!$leaderRow = DB::table('user_group')->where('user_id',$leader_id)->first()){
            return false;
        }

        $update = [
            'group_id' => $leaderRow->group_id,
        ];

        $leader_level = $leaderRow->level;
        for($i = $leader_level; $i <=5 ; $i++){
            $lv = 'lv_'.$i;
            $update[$lv] = $leaderRow->$lv;
        }

        try {
            DB::table('user_group')->where('group_id',$this->id)->update($update);
        } catch (\Throwable $th) {
            return false;
            //throw $th;
        }
        
        return true;

    }

    public function groupLevel(){
        
        if($row = DB::table('user_group')->select('level')->where('user_id',$this->id)->first()){
            return $row->level;
        }
        return 0;

    }

    public function isPrimaryLeaderOfGroup(){
        if(!$row = DB::table('user_group')->select('group_id')->where('user_id',$this->id)->first()){
            return false;
        }
        if($row->group_id != $this->id){
            return false;
        }
        return true;
    }

    public function makeGroupLeader($level){
        $insert = [
            'group_id'=>$this->id,
            'user_id'=>$this->id,
            'level'=>$level,
        ];
        $lv = 'lv_'.$level;
        $insert[$lv] = $this->id;
        try {
            DB::table('user_group')->insert($insert);
            $this->org_rank = $level;
            $this->save();
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }
    public function removeGroupLeader($user_level){
        $lv = 'lv_' . $user_level;
        try {
            DB::table('user_group')->where($lv,$this->id)->delete();
            $this->org_rank = null;
            $this->save();
        } catch (\Throwable $th) {
            return false;
        }
        return true;
    }
    public function getGroupUsers($above_level=1){
        $user_rows = $this->getGroupUserRows($above_level);
        $userIdArray = [];
        foreach ($user_rows as $row) {
            $userIdArray[] = $row->user_id;
        }
        $users = $this->whereIn('id',$userIdArray)->get();
        return $users;
    }
    public function getGroupUserRows($above_level=1)
    {
        if(!$row = DB::table('user_group')->select('group_id')->where('user_id',$this->id)->first()){
            return [];
        }

        $query = DB::table('user_group')->where('group_id',$row->group_id);
        if($above_level > 1){
            $query->where('level','>=',$above_level);
        }
        $group_users = $query->orderBy('level','desc')->get();

        return $group_users;

    }

    public function payHistory()
    {
        return $this->hasMany('App\PayDate','user_id');
    }
  
    //database relationship binding

    public function updateWallet($give_take,$amount)
    {
        if ($give_take) {
            $this->wallet += $amount;
        }else{
            $this->wallet -= $amount;
        }
        $this->save();
    }









    //user roles helper

    /**
    * @param string|array $roles
    */
    public function authorizeRoles($roles)
    {
    if (is_array($roles)) {
        return $this->hasAnyRole($roles) || 
                abort(401, 'This action is unauthorized.');
    }
    return $this->hasRole($roles) || 
            abort(401, 'This action is unauthorized.');
    }
    /**
    * Check multiple roles
    * @param array $roles
    */
    public function hasAnyRole($roles)
    {
        return null !== $this->roles()->whereIn('name', $roles)->first();
    }
    /**
    * Check one role
    * @param string $role
    */
    public function hasRole($role)
    {
        return null !== $this->roles()->where('name', $role)->first();
    }

    public function go_events(){
        return $this->belongsToMany('App\Event','event_users','user_id','event_id');
    }

    public function becomeRole($role_name){
        if(!$role = DB::table('roles')->where('name',$role_name)->first()){
            return false;
        }

        if($role_user = DB::table('role_user')->where('user_id',$this->id)->where('role_id',$role->id)->first()){
            return true;
        }

        try {
            DB::table('role_user')->insert([
                'role_id'=>$role->id,
                'user_id'=>$this->id
            ]);
            $this->role = $role->id;
            $this->save();
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

}
