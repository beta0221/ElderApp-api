<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\PayDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class MemberController extends Controller
{

    public function __construct()
    {
        $this->middleware('JWT', ['except' => ['create','welcome','inviterCheck','cacu','store','memberTree','updateMemberLevel']]);
    }

    //管理後台的搜尋會員請求
    public function searchMember(Request $request)
    {
        $searchColumn = ($request->searchColumn)?$request->searchColumn:'id_number';
        
        if($request->searchColumn == 'name' && !empty($request->searchText)){
            $user = User::where('name','like',"%$request->searchText%")->get();
        }else{
            $user = User::where($searchColumn,$request->searchText)->get();
        }
        return response()->json($user);
    }


    //管理後台使用的請求
    public function getMembers(Request $request)
    {

        $page = $request->page;
        $rows = $request->rowsPerPage;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';
        if ($request->descending == null || $request->descending == 'false') {
            $ascOrdesc = 'asc';
        }
        $orderBy = ($request->sortBy) ? $request->sortBy : 'id';
        $column = ($request->column) ? $request->column : null;
        $value = (isset($request->value)) ? $request->value : null;

        $query = DB::table('users')
            ->select('id','name','org_rank','email','tel','phone','gender','rank','inviter','inviter_phone','pay_status','created_at','expiry_date','valid','birthdate','id_number','id_code')
            ->orderBy($orderBy, $ascOrdesc);
        $count = DB::table('users');

        if($column != null && $value != null){
            $query->where($column,$value);
            $count->where($column,$value);
        }    

        $users = $query->skip($skip)->take($rows)->get();
        $total = $count->count();

        return response()->json([
            'users' => $users,
            'total' => $total,
        ]);
    }

    public function create()
    {
        return view('member.join');
    }

    //會員資料整理（無用）
    public function cacu()
    {
        if (empty($_GET['from']) || empty($_GET['to'])) {
            return response('no paremeters');
        }
        $from = (int)$_GET['from'];
        $to = (int)$_GET['to'];
        try {
            $users = User::whereBetween('id',[$from,$to])->get();
            foreach($users as $user){
                $id = $user->id;
                $user = User::find($id);
                while (strlen($id) < 4) {
                    $id = '0'.$id;
                }
                $m = substr($user->created_at,5,2);
                $id_code = 'H' . substr(date('Y'),-2) . $m . $id . rand(0,9);
                $user->id_code = $id_code;  

                $user->password = $user->email;
                $user->save();
            }
        } catch (\Throwable $th) {
            return response($th);
        }
        return response('success');
    }

    //註冊新會員
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|unique:users',
            'password' => 'required',
            'name' => 'required',
            'gender' => 'required',
            'birthdate'=>'required',
            'id_number'=>'required',
            'district_id' => 'required',
            'address' => 'required',
            'pay_method'=>'required',
        ]);

        if($request->pay_method == 1){
            $inviter = User::where('phone',$request->inviter_id_code)->orWhere('id_code',$request->inviter_id_code)->firstOrFail();
            $request->merge([
                'inviter_id'=>$inviter->id,
                'inviter'=>$inviter->name,
                'inviter_phone'=>$inviter->phone,
            ]);    
        }
        
        $request->merge(['pay_status'=>1,]);
        $id_code = $this->generateIdCode();
        $request->merge([
            'id_code'=>$id_code
        ]);
        
        try {
            $user = User::create($request->all());
            
        } catch (\Throwable $th) {
            return response($th);
        }


        if($request->pay_method == 1){
            try {
                $row = DB::table('user_group')->where('user_id',$inviter->id)->first();
                if($row){
                    DB::table('user_group')->insert([
                        'group_id'=>$row->group_id,
                        'user_id'=>$user->id,
                        'level'=>1,
                        'lv_1'=>$user->id,
                        'lv_2'=>$row->lv_2,
                        'lv_3'=>$row->lv_3,
                        'lv_4'=>$row->lv_4,
                        'lv_5'=>$row->lv_5,
                    ]);
                }
                
            } catch (\Throwable $th) {
                return response($th);
            }
        }


        if($request->app){
            return response()->json([
                's'=>1,
                'user'=>$request,
            ]);
        }else{
            return view('member.welcome',['id_code'=>$id_code]);
        }

        
    }


    private function generateIdCode(){
        $lastId = User::select('id')->orderBy('id','desc')->first()->id;
        if(!$lastId){
            $lastId = 0;
        }
        $nextId = $lastId + 1;
        while (strlen($nextId) < 4) {
            $nextId = '0'.$nextId;
        }
        $id_code = 'H' . substr(date('Y'),-2) . date('m') . $nextId . rand(0,9);
        return $id_code;
    }

    //更新會員付款狀態
    public function changePayStatus(Request $request){
        date_default_timezone_set('Asia/Taipei');
        $user = User::where('id',$request->id)->firstOrFail();
        $p = $user->pay_status;

        if($p == 2){
            if(!Auth::user()->hasRole('accountant')){
                return response([
                    's'=>0,
                    'm'=>'權限不足，此操作限會計人員。'
                ]);
            }
        }

        if ($p < 3) {
            $p++;
            $user->pay_status =$p;
        }

        if($p == 3){
            $user->expiry_date = date('Y-m-d', strtotime('+1 years'));
            $user->valid = 1;
            PayDate::create(['user_id'=>$request->id]);
        }
        $user->save();
        return response(['s'=>1,'m'=>'Updated','d'=>date('Y-m-d', strtotime('+1 years'))],Response::HTTP_ACCEPTED);
    }

    //取得會員付款歷史紀錄
    public function getPayHistory($id){
        $user = User::findOrFail($id);
        return response()->json($user->payHistory()->get());
    }

    //取得會員詳細基本資料
    public function getMemberDetail($id){
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    //檢查所有會員把過期的會員效期變成過期
    public function executeExpired(Request $request){
        date_default_timezone_set('Asia/Taipei');
        $now = strtotime(date('Y-m-d'));
        DB::update("update users set valid = 0 WHERE UNIX_TIMESTAMP(expiry_date) < $now");
        return response(['s'=>1,'m'=>'Updated'],Response::HTTP_ACCEPTED);
    }

    //更新會員資格（無效會員變成有效會員）
    public function toValid(Request $request){
        date_default_timezone_set('Asia/Taipei');
        $user = User::where('id',$request->id)->firstOrFail();
        if($user->valid == 0 && $user->expiry_date != null){
            $user->update([
                'valid'=>1,
                'expiry_date'=>date('Y-m-d',strtotime('+1 years')),
                ]);
            PayDate::create(['user_id'=>$request->id]);
            return response(['s'=>1,'m'=>'Updated','d'=>date('Y-m-d')],Response::HTTP_ACCEPTED);
        }else{
            return response(['s'=>0,'m'=>'not allowed to valid'],Response::HTTP_ACCEPTED);
        }
    }

    //檢查推薦人是否存在
    public function inviterCheck(Request $request){
        
        $request->validate([
            'inviter_id_code'=>'required',
        ]);
        $inviter = User::where('id_code',$request->inviter_id_code)->first();
        
        if(!$inviter){
            $inviter = User::where('phone',$request->inviter_id_code)->first();
        }

        if(!$inviter){
            return response()->json([
                's'=>0,
            ]);
        }else{
            return response()->json([
                's'=>1,
                'inviter'=>$inviter->name,
            ]);
        }
    }


    //大天使小天使加入組織請求
    public function addGroupMember(Request $request){

        // leader_id
        //user_id
        //level

        $leader = User::find($request->leader_id);
        $addUser = User::find($request->user_id);

        if(!$addUser || !$leader){
            return response()->json([
                's'=>0,'m'=>'會員帳號不存在',
            ]);
        }

        if($request->leader_id == $request->user_id){
            return response()->json([
                's'=>0,'m'=>'無效的操作',
            ]);
        }

        $leader_level = $leader->groupLevel();
        if($leader_level <= 0){
            return response()->json([
                's'=>0,'m'=>'所選的使用者無所屬組織。',
            ]);
        }

        if($request->level >= $leader_level){
            return response()->json([
                's'=>0,'m'=>'所選等級不能高於使用者等級',
            ]);
        }

        if($addUser->groupLevel() <= 0){
            if(!$result = $addUser->joinToGroup($leader->id,$request->level)){
                return response()->json([
                    's'=>0,'m'=>'系統錯誤',
                ]);
            }
        }else if($addUser->isLeaderOfGroup()){

            if($addUser->groupLevel() != $request->level){
                return response()->json([
                    's'=>0,'m'=>'遷移組織只能指派為目前職位',
                ]);
            }

            if(!$result = $addUser->mergeToGroup($leader->id)){
                return response()->json([
                    's'=>0,'m'=>'系統錯誤',
                ]);
            }
        }else{
            return response()->json([
                's'=>0,'m'=>'此會員已經存在所屬組織。',
            ]);
        }

        return response()->json([
            's'=>1,'m'=>'新增成功',
        ]);

    }

    public function getUserLevel($user_id){

        if($user = User::find($user_id)){
            return response()->json($user->groupLevel());
        }
        return 0;
    }

    public function makeGroupLeader(Request $request){
        //user_id
        //level
        if(!$user = User::find($request->user_id)){
            return response()->json([
                's'=>0,'m'=>'查無使用者',
            ]);
        }
        if($user->groupLevel() > 0){
            return response()->json([
                's'=>0,'m'=>'此使用者目前已存在職位。',
            ]);
        }
        if(!$result = $user->makeGroupLeader($request->level)){
            return response()->json([
                's'=>0,'m'=>'系統錯誤',
            ]);
        }
        return response()->json([
            's'=>1,'m'=>'指派成功',
        ]);
    }

    public function makeTeacher(Request $request){
        //user_id
        if(!$user = User::find($request->user_id)){
            return response()->json([
                's'=>0,'m'=>'查無使用者',
            ]);
        }
        if(!$result = $user->becomeRole('teacher')){
            return response()->json([
                's'=>0,'m'=>'系統錯誤',
            ]);
        }

        return response()->json([
            's'=>1,'m'=>'指派成功',
        ]);
    }

    //App 的 AccountPage OnAppearing 請求
    public function myAccount(){
        return response()->json(Auth::user());
    }

    //App首頁更新基本資料
    public function updateAccount(Request $request){
        $user = Auth::user();
        
        if(!$user){
            return response()->json([
                's'=>0,
                'm'=>'身份驗證失敗請重新登入',
            ]);
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->tel = $request->tel;
        $user->address = $request->address;
        $user->id_number = $request->id_number;
        $user->save();

        return response()->json([
            's'=>1,
            'm'=>'成功更新資料',
        ]);
    }

    public function updateMemberAccount(Request $request){
        $user = User::find($request->id);

        if(!$user){
            return response()->json([
                's'=>0,
                'm'=>'使用者不存在',
            ]);
        }

        if($user->email != $request->email){
            if(User::where('email',$request->email)->first()){
                return response()->json([
                    's'=>0,
                    'm'=>'帳號已存在',
                ]);
            }
            $user->email = $request->email;
        }
        

        $user->name = $request->name;
        $user->id_number = $request->id_number;
        $user->birthdate = $request->birthdate;
        $user->phone = $request->phone;
        $user->tel = $request->tel;
        $user->address = $request->address;
        $user->valid = $request->valid;
        $user->invoice = $request->invoice;
        $user->save();

        return response()->json([
            's'=>1,
            'm'=>'成功更新資料',
        ]);
    }

    public function updateMemberPassword(Request $request,$id_code){
        
        if($request->adminCode == 'ji3g4ej03xu3m06'){

            $user = User::where('id_code',$id_code)->firstOrFail();
            $user->update([
                'password'=>$request->password
            ]);

            return response('success',200);
        }
        return response('權限不足',400);


        
    }

    //會員續會申請
    public function extendMemberShip(Request $request){
        $user = User::find($request->user_id);
        if(!$user){
           return response('error',400);
        }

        if($user->valid != 0){
            return response()->json([
                's'=>0,
                'm'=>'您的會員效期尚未到期。',
            ]);
        }

        if($user->pay_status == 1){
            return response()->json([
                's'=>1,
                'm'=>'我們已收到您的續會申請，將會請工作人員向您聯繫。',
            ]);
        }

        try {
            $user->pay_status = 1;
            $user->save();
            return response()->json([
                's'=>1,
                'm'=>'我們已收到您的續會申請，將會請工作人員向您聯繫。',
            ]);
        } catch (\Throwable $th) {
            return response($th);
        }
    }

    public function memberTree($id_code){
        $user = User::where('id_code',$id_code)->first();
        if(!$user){
            return response('此使用者會員編號不存在');
        }
        $group_users = $user->getGroupUsers();
        if(count($group_users)<=0){
            return response('使用者並無所屬組織。');
        }


        $dic=[];
        $validDic=[];
        foreach ($group_users as  $g_user) {
            if($_user = User::select('name','valid')->where('id',$g_user->user_id)->first()){
                $dic[$g_user->user_id] = $_user->name;
                $validDic[$g_user->user_id] = $_user->valid;
            }
        }

        return view('member.tree',[
            'user_id'=>$user->id,
            'group_users'=>json_encode($group_users),
            'name_dic'=>json_encode($dic),
            'valid_dic'=>json_encode($validDic)
        ]);

    }

    public function updateMemberLevel(Request $request){

        $this->validate($request,[
            'user_id'=>'required',
            'level'=>'required|numeric|min:1|max:5'
        ]);

        $row = DB::table('user_group')->where('user_id',$request->user_id)->select('group_id','level')->first();
        if(!$row){
            return response()->json([
                's'=>0,
                'm'=>'使用者不存在'
            ]);
        }

        $old_level = $row->level;
        $old_lv = "lv_" .  $row->level;

        if($old_level == 5){
            return response()->json([
                's'=>0,
                'm'=>'無法更改此用戶等級。'
            ]);
        }

        $lv = "lv_" . $request->level;

        try {
            DB::table('user_group')->where('user_id',$request->user_id)->update([
                'level'=>$request->level,
                $lv => $request->user_id
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                's'=>0,
                'm'=>'系統錯誤',
            ]);
        }

        try {
            DB::table('user_group')->where('group_id',$row->group_id)->where($old_lv,$request->user_id)->update([
                $old_lv=>null
            ]);
        } catch (\Throwable $th) {
            DB::table('user_group')->where('user_id',$request->user_id)->update([
                'level'=>$old_level,
                $lv => null
            ]);
            return response()->json([
                's'=>0,
                'm'=>'系統錯誤.error code : 2781',
            ]);
        }

        return response()->json([
            's'=>1,
            'm'=>'更新成功'
        ]);
    }

    public function welcome(){
        return view('member.welcome');
    }
    
}


