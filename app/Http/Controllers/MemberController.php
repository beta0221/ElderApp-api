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
        $this->middleware('JWT', ['except' => ['create','welcome','inviterCheck','cacu','store']]);
    }


    //管理後台的搜尋會員請求
    public function searchMember(Request $request)
    {
        $searchColumn = ($request->searchColumn)?$request->searchColumn:'id_number';
        $user = User::where($searchColumn,$request->searchText)->get();
        if(count($user)<=0){
            $user = User::where('name','like',"%$request->searchText%")->get();
        }
        return response()->json($user);
    }

    //管理後台使用的請求
    public function getMembers(Request $request)
    {
        $page = $request->page;
        $rows = $request->rowsPerPage;
        $skip = ($page - 1) * $rows;
        if ($request->descending == null || $request->descending == 'false') {
            $ascOrdesc = 'asc';
        } else {
            $ascOrdesc = 'desc';
        }
        $orderBy = ($request->sortBy) ? $request->sortBy : 'id';

        $users = DB::table('users')
            ->select('id', 'name','org_rank','email','tel','phone','gender', 'rank', 'inviter', 'inviter_phone', 'pay_status', 'created_at', 'last_pay_date','valid','birthdate','id_number')
            ->orderBy($orderBy, $ascOrdesc)
            ->skip($skip)
            ->take($rows)
            ->get();

        $total = DB::table('users')->count();

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
                // while (strlen($id) < 4) {
                //     $id = '0'.$id;
                // }
                // $m = substr($user->created_at,5,2);
                // $id_code = 'H' . substr(date('Y'),-2) . $m . $id . rand(0,9);
                // $user->id_code = $id_code;  

                // $user->password = bcrypt($user->email);
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
        // $request->flash();
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
            $inviter = User::where('id_code',$request->inviter_id_code)->first();
            if(!$inviter){
                $inviter = User::where('phone',$request->inviter_id_code)->firstOrFail();
            }
            $request->merge([
                'inviter'=>$inviter->name,
                'inviter_phone'=>$inviter->phone,
            ]);    
        }
        
        $request->merge(['pay_status'=>1,]);
        
        try {
            
            $lastId = User::select('id')->orderBy('id','desc')->first()->id;
            if(!$lastId){
                $lastId = 0;
            }
            $nextId = $lastId + 1;

            while (strlen($nextId) < 4) {
                $nextId = '0'.$nextId;
            }
            $id_code = 'H' . substr(date('Y'),-2) . date('m') . $nextId . rand(0,9);

            $request->merge([
                'id_code'=>$id_code
            ]);

            $user = User::create($request->all());
            $user->roles()->attach(Role::where('name', 'user')->first());
        } catch (\Throwable $th) {
            return response($th);
        }

        if($request->app){
            return response()->json([
                's'=>1,
                'user'=>$request,
            ]);
        }

        return view('member.welcome',['id_code'=>$id_code]);
    }

    //更新會員付款狀態
    public function changePayStatus(Request $request){
        $user = User::where('id',$request->id)->firstOrFail();
        $p = $user->pay_status;
        if ($p < 3) {
            $p++;
            $user->pay_status =$p;
        }

        if($p == 3){
            $user->last_pay_date = date('Y-m-d');
            $user->valid = 1;
            PayDate::create(['user_id'=>$request->id]);
        }
        $user->save();
        return response(['s'=>1,'m'=>'Updated','d'=>date('Y-m-d')],Response::HTTP_ACCEPTED);
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
        DB::update('update users set valid = 0 WHERE last_pay_date < DATE_SUB(NOW(),INTERVAL 1 YEAR)');
        return response(['s'=>1,'m'=>'Updated'],Response::HTTP_ACCEPTED);
    }

    //更新會員資格（無效會員變成有效會員）
    public function toValid(Request $request){
        $user = User::where('id',$request->id)->firstOrFail();
        if($user->valid == 0 && $user->last_pay_date != null){
            $user->update([
                'valid'=>1,
                'last_pay_date'=>date('Y-m-d'),
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

    //大天使小天使取得自己組織的成員
    public function getMemberGroupMembers($id){
        $user = User::find($id);
        if($user->org_rank==2){
            $groupMembers = $user->groupUsers()->get();
        }elseif($user->org_rank==1){
            $groupMembers = $user->groupUsers()->get();
        }

        return response()->json([
            's'=>1,
            'groupMembers'=>$groupMembers,
        ]);
    }

    //大天使小天使加入組織請求
    public function addGroupMember(Request $request){
        $leader = User::find($request->leaderId);
        $addUser = User::where('email',$request->addAccount)->first();

        if($addUser){
            try {
                if ($leader->org_rank==2) {
                    if($addUser->org_rank!=1){
                        return response()->json([
                            's'=>-1,
                            'm'=>'會員階級不符合'
                        ]);
                    }
                }

                if($leader->org_rank==1){
                    if($addUser->org_rank>=1){
                        return response()->json([
                            's'=>-1,
                            'm'=>'會員階級不符合'
                        ]);
                    }
                }

                if(!$leader->groupUsers()->find($addUser->id)){
                    $leader->groupUsers()->attach($addUser->id);
                    return response()->json([
                        's'=>1,
                        'addUser'=>$addUser,
                    ]);
                }else{
                    return response()->json([
                        's'=>-1,
                        'm'=>'會員帳號已經存在該組織',
                    ]);
                }
                
            } catch (\Throwable $th) {
                return response($th);
            }
    
        }else{
            return response()->json([
                's'=>0,
                'm'=>'會員帳號不存在',
            ]);
        }
    }

    public function deleteGroupMember(Request $request){
        $leader = User::find($request->leaderId);
        $deleteUser = User::find($request->deleteAccountId);

        if($leader != null && $deleteUser != null){
            $leader->groupUsers()->detach($deleteUser->id);
        }

        return response()->json([
            's'=>1,
            'addUser'=>$deleteUser,
        ]);

    }

    //App 的 AccountPage OnAppearing 請求
    public function myAccount(){
        return response()->json(Auth::user());
    }

    //App首頁更新基本資料
    public function updateAccount(Request $request){
        $user = User::find(Auth::user()->id);

        if($user){
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->tel = $request->tel;
            $user->address = $request->address;
            $user->id_number = $request->id_number;
            $user->save();
        }

        return response()->json([
            's'=>1,
            'm'=>'成功更新資料',
        ]);
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

    public function welcome(){
        return view('member.welcome');
    }
    
}
