<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserDetial;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    public function getMembers()
    {
        $users = DB::table('users')
        ->join('user_detials','users.id','=','user_detials.id')
        ->select('name','email','gender','rank','inviter','inviter_phone','pay_status','join_date','last_pay_date')
        ->get();

        return response()->json($users);
    }
}
