<?php

namespace App;

use App\Jobs\NotifyAppUser;
use Illuminate\Notifications\Notifiable;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
        'association_id',
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

    public function locations(){
        return $this->belongsToMany('App\Location','location_manager','user_id','location_id');
    }

    public function certificates(){
        return $this->belongsToMany('App\EventCertificate','user_certificate','user_id','certificate_id');
    }

    public function manage_events(){
        return $this->belongsToMany('App\Event','event_manager','user_id','event_id');
    }

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

    public function isSameAssociation(){
        $request_user = request()->user();
        if($request_user->isAdmin()){
            return true;
        }
        if($request_user->association_id == $this->association_id){
            return true;
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

    /**提升組長等級 */
    public function promoteGroupLeader(int $target_level){
        $from_lv = 'lv_' . $this->groupLevel();
        $to_lv = 'lv_' . $target_level;
        try {
            DB::table('user_group')->where('group_id',$this->id)->update([
                $from_lv => null,
                $to_lv => $this->id,
            ]);
            DB::table('user_group')->where('user_id',$this->id)->update([
                'level' => $target_level
            ]);
            $this->org_rank = $target_level;
            $this->save();
        } catch (\Throwable $th) {
            Log::info($th);
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

    /**
     * 取得使用者
     */
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

    /**
     * 取得使用者等級以下的組織人員
     */
    public function getSubGroupUserRows(){
        if(!$row = DB::table('user_group')->where('user_id',$this->id)->first()){ return []; }
        $level = 'lv_' . $row->level;
        $group_users = DB::table('user_group')->where($level,$row->user_id)->where('level','<',$row->level)->get();
        return $group_users;
    }

    public function payHistory()
    {
        return $this->hasMany('App\PayDate','user_id');
    }
  
    //database relationship binding

    /**
     * 提升經驗值
     */
    public function increaseRank($value){
        $this->rank += $value;
        $this->save();
    }

    /**
     * 更新使用者錢包
     * @param bool $give_take 增加或減少 (INCREASE_WALLET,DECREASE_WALLET)
     * @param Int $amount 多少樂幣
     */
    public function updateWallet($give_take,$amount)
    {
        if ($give_take) {
            $this->wallet += $amount;
        }else{
            $this->wallet -= $amount;
        }
        $this->save();
    }

    /**
     * 更新使用者錢包 並且同時新增交易紀錄
     * @param bool $give_take 增加或減少 (INCREASE_WALLET,DECREASE_WALLET)
     * @param Int $amount 多少樂幣
     * @param String $event 交易事件留言
     * @param Int $target_id 交易目標（預設為 0:系統）
     */
    public function update_wallet_with_trans($give_take,$amount,$event,$target_id=0){
        $this->updateWallet($give_take,$amount);
        $tran_id = time() . rand(10,99);
        Transaction::create([
            'tran_id'=>$tran_id,
            'user_id'=>$this->id,
            'event' =>$event,
            'amount'=>$amount,
            'target_id'=>$target_id,
            'give_take'=>$give_take,
        ]);
    }

    /**
     * 儲存使用者的推播 token
     */
    public function set_pushtoken($pushtoken){
        if($row = DB::table('user_pushtoken')->where('user_id',$this->id)->first()){
            DB::table('user_pushtoken')->where('user_id',$this->id)->update([
                'pushtoken'=>$pushtoken
            ]);
        }else{
            DB::table('user_pushtoken')->insert([
                'user_id'=>$this->id,
                'pushtoken'=>$pushtoken
            ]);
        }
    }
    public function remove_pushtoken(){
        DB::table('user_pushtoken')->where('user_id',$this->id)->delete();
    }

    public static function getNameDictByIdArray($userIdArray){
        $userList = User::select('id','name')->whereIn('id',$userIdArray)->get();
        $dict = [];
        foreach ($userList as $user) {
            $dict[$user->id] = $user->name;
        }
        return $dict;
    }

    /**發送獎勵給推薦人（如果有推薦人的話） */
    public function rewardInviter(){
        if($this->inviter_id){
            if($inviter = User::find($this->inviter_id)){
                $event = "推薦獎勵:" . $this->name;
                $inviter->update_wallet_with_trans(User::INCREASE_WALLET,300,$event);
            }
        }
    }
    /**續會的推薦人獎勵 */
    public function rewardInviterForRenew(){
        if($this->inviter_id){
            if($inviter = User::find($this->inviter_id)){
                $event = "推薦人-會員續會獎勵:" . $this->name;
                $inviter->update_wallet_with_trans(User::INCREASE_WALLET,100,$event);
            }
        }
    }

    /** 發送獎勵給組織中的各個職位 */
    public function rewardGroupMembers(){
        if(!$group = DB::table('user_group')->where('user_id',$this->id)->first()){ return; }
        for ($i = $group->level + 1; $i <= 5; $i++) { 
            $lv = 'lv_' . $i;
            if(!$group->{$lv}){ continue; }
            if(!$user = User::find($group->{$lv})){ continue; }
            $event = '';
            $reward = 0;
            switch ($i) {
                case 2:
                    $event = "小天使-服務會員獎勵:" . $this->name;
                    $reward = 200;
                    break;
                case 3:
                    $event = "大天使-服務會員獎勵:" . $this->name;
                    $reward = 100;
                    break;
                case 4:
                    $event = "守護天使-服務會員獎勵:" . $this->name;
                    $reward = 100;
                    break;
                case 5:
                    $event = "領航天使-服務會員獎勵:" . $this->name;
                    $reward = 50;
                    break;
                default:
                    break;
            }
            $user->update_wallet_with_trans(User::INCREASE_WALLET,$reward,$event);
        }
    }

    /** 發送續會服務獎勵給組織中的各個職位 */
    public function rewardGroupMembersForRenew(){
        if(!$group = DB::table('user_group')->where('user_id',$this->id)->first()){ return; }
        for ($i = $group->level + 1; $i <= 5; $i++) { 
            $lv = 'lv_' . $i;
            if(!$group->{$lv}){ continue; }
            if(!$user = User::find($group->{$lv})){ continue; }
            $event = '';
            $reward = 100;
            switch ($i) {
                case 2:
                    $event = "小天使-會員續會獎勵:" . $this->name;
                    break;
                case 3:
                    $event = "大天使-會員續會獎勵:" . $this->name;
                    break;
                case 4:
                    $event = "守護天使-會員續會獎勵:" . $this->name;
                    break;
                case 5:
                    $event = "領航天使-會員續會獎勵:" . $this->name;
                    break;
                default:
                    break;
            }
            $user->update_wallet_with_trans(User::INCREASE_WALLET,$reward,$event);
        }
    }


    /**頒發結業證書 */
    public function issueCertificate(EventCertificate $cert){
        $this->certificates()->attach($cert->id);
        $this->update_wallet_with_trans(User::INCREASE_WALLET,$cert->reward,$cert->getTitle());
        NotifyAppUser::dispatch($this->id,'恭喜您！','您獲得了畢業證書及獎勵。');
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

    /**
     * 使用者參加的活動Id Array
     * @return array Event id array
     */
    public function getUserEventsIdArray(){
        $eventIdArray = DB::table('event_users')->where('user_id',$this->id)->pluck('event_id');
        return $eventIdArray;
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

    public function removeLocationManagerRole(){
        $role = Role::where('name','location_manager')->first();
        $this->roles()->detach($role->id);
    }

}
