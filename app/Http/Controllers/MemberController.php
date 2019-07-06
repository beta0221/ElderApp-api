<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\UserDetial;
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
        }else {
            $ascOrdesc = 'desc';
        }
        
        $orderBy = ($request->sortBy)?$request->sortBy:'id';

        $users = DB::table('users')
        ->join('user_detials','users.id','=','user_detials.id')
        ->select('users.id','name','email','gender','rank','inviter','inviter_phone','pay_status','join_date','last_pay_date')
        ->orderBy($orderBy,$ascOrdesc)
        ->skip($skip)
        ->take($rows)
        ->get();

        $total = DB::table('users')->count();

        return response()->json([
            'users'=>$users,
            'total'=>$total,
        ]);
    }
}
