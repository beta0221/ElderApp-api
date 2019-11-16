<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements JWTSubject
{
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


    // public function groupUsers()
    // {
    //     return $this->belongsToMany('App\User','user_group','leader_user_id','user_id');
    // }

    public function joinToGroup($leader_id,$level){
        $row = DB::table('user_group')->where('user_id',$leader_id)->first();
        if($row){

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
            } catch (\Throwable $th) {
                throw $th;
            }
        }
    }

    public function groupLevel(){
        
        if($row = DB::table('user_group')->select('level')->where('user_id',$this->id)->first()){
            return $row->level;
        }
        return 0;

    }

    public function getGroupUsers()
    {
        if(!$row = DB::table('user_group')->select('group_id')->where('user_id',$this->id)->first()){
            return [];
        }

        $group_users = DB::table('user_group')->where('group_id',$row->group_id)->orderBy('level','desc')->get();

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
        return null !== $this->roles()->whereIn(‘name’, $roles)->first();
    }
    /**
    * Check one role
    * @param string $role
    */
    public function hasRole($role)
    {
        return null !== $this->roles()->where(‘name’, $role)->first();
    }

    public function go_events(){
        return $this->belongsToMany('App\Event','event_users','user_id','event_id');
    }

}
