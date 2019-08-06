<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\PayDate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class MemberController extends Controller
{
    public function searchMember(Request $request)
    {
        $user = User::where('id_code',$request->searchText)->get();
        if(count($user)<=0){
            $user = User::where('name','like',"%$request->searchText%")->get();
        }

        return response()->json($user);

    }

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
            ->select('id', 'name','org_rank','email', 'gender', 'rank', 'inviter', 'inviter_phone', 'pay_status', 'created_at', 'last_pay_date','valid')
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

    public function store(Request $request)
    {
        $request->flash();
        $request->validate([
            'email' => 'required|unique:users',
            'password' => 'required',
            'name' => 'required',
            'gender' => 'required',
            'birthdate'=>'required',
            'id_number'=>'required',
            'district_id' => 'required',
            'address' => 'required',
            // 'inviter' => 'required',
            // 'inviter_id_code' => 'required',
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
        
        $request->merge([
            'pay_status'=>1,
        ]);
        
        try {
            $user = User::create($request->all());
            $user->roles()->attach(Role::where('name', 'user')->first());
            $id = $user->id;

            while (strlen($id) < 4) {
                $id = '0'.$id;
            }

            $id_code = 'H' . substr(date('Y'),-2) . date('m') . $id . rand(0,9);

            $user->update(['id_code'=>$id_code]);

        } catch (\Throwable $th) {
            return response($th);
        }

        return view('member.welcome',['id_code'=>$id_code]);
    }

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

    public function getPayHistory($id){

        $user = User::findOrFail($id);
        

        return response()->json($user->payHistory()->get());
    }

    public function getMemberDetail($id){
        
        $user = User::findOrFail($id);

        return response()->json($user);

    }

    public function executeExpired(Request $request){

        DB::update('update users set valid = 0 WHERE last_pay_date < DATE_SUB(NOW(),INTERVAL 1 YEAR)');
        
        return response(['s'=>1,'m'=>'Updated'],Response::HTTP_ACCEPTED);
    }

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

    public function addGroupMember(Request $request){
        $leader = User::find($request->leaderId);
        $addUser = User::where('email',$request->addAcount)->first();

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

    public function welcome(){
        return view('member.welcome');
    }
    
}
