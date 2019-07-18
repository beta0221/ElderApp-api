<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use Illuminate\Support\Facades\DB;

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

        try {
            $user = User::create($request->all());
            $user->roles()->attach(Role::where('name', 'user')->first());
        } catch (\Throwable $th) {
            return response($th);
        }

        return view('member.welcome');
    }

    // public function welcome(){
    //     return view('member.welcome');
    // }
    
}
