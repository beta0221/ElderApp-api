<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class MemberController extends Controller
{
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
            ->select('id', 'name', 'email', 'gender', 'rank', 'inviter', 'inviter_phone', 'pay_status', 'join_date', 'last_pay_date')
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
            'district_id' => 'required',
            'address' => 'required',
            'inviter' => 'required',
            'inviter_phone' => 'required',
        ]);

        $request->merge([
            'pay_status'=>1,
        ]);
        
        try {
            $user = User::create($request->all());
            $user->roles()->attach(Role::where('name', 'user')->first());
        } catch (\Throwable $th) {
            return response($th);
        }

        return view('member.welcome');
    }

    public function changePayStatus(Request $request){
        $user = User::where('id',$request->id)->firstOrFail();
        $p = $user->pay_status;
        if ($p == 3) {
            $p = 0;
        }else{
            $p++;
        }
        $user->update(['pay_status'=>$p]);

        // $user->pay_status = $p;
        // $user->save();

        return response(['s'=>1,'m'=>'Updated'],Response::HTTP_ACCEPTED);

    }

    // public function welcome(){
    //     return view('member.welcome');
    // }
    
}
