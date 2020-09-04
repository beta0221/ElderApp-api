<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['JWT','admin']);
    }


    public function getTotal(){
        return User::count();
    }
    public function getValid(){
        return User::where('valid',1)->count();
    }
    public function getUnValid(){
        return User::where('valid',0)->count();
    }
    public function getAgeDist(){
        $ageDist = [
            '50-55'=>0,
            '55-60'=>0,
            '60-65'=>0,
            '65-100'=>0,
        ];
        foreach ($ageDist as $age => $sum) {
            $ageArray = explode('-',$age);
            $from = $ageArray[0];
            $to = $ageArray[1];
            $year = date('Y');
            $count = User::whereYear('birthdate','<=',$year - (int)$from)->whereYear('birthdate','>',$year - (int)$to)->count();
            $ageDist[$age] = $count;
            sleep(0.3);
        }
        return response($ageDist);
    }
    public function getTotalWallet(){
        $totalWallet = DB::table('users')->sum('wallet');
        return response($totalWallet);
    }

    public function getBirthdayList(){
        date_default_timezone_set('Asia/Taipei');
        $users = User::whereMonth('birthdate',date('m'))->whereDay('birthdate',date('d'))->where('valid',1)->get();
        $userList = [];
        foreach ($users as $user) {
            $userList[] = $user->name;
        }
        return response($userList);
    }
    //
    public function getOrgRankSum5(){
        $count = User::where('org_rank',5)->count();
        return response($count);
    }
    public function getOrgRankSum4(){
        $count = User::where('org_rank',4)->count();
        return response($count);
    }
    public function getOrgRankSum3(){
        $count = User::where('org_rank',3)->count();
        return response($count);
    }
    public function getOrgRankSum2(){
        $count = User::where('org_rank',2)->count();
        return response($count);
    }
    public function getOrgRankSum1(){
        $count = User::where('org_rank',1)->count();
        return response($count);
    }
    //
    public function getDistrictSum(){
        $dict = [];
        for ($i=1; $i < 13 ; $i++) { 
            $count = User::where('district_id',$i)->count();
            $dict[$i] = $count;
            sleep(0.1);
        }
        return response($dict);
    }


    public function getGroupleaders(){
        $groupLeaders = DB::table('user_group')->select('group_id')->groupBy('group_id')->pluck('group_id');
        return response($groupLeaders);
    }
    public function getGroupStatus(Request $req){
        $user = User::findOrFail($req->leader_id);
        $members_id = DB::table('user_group')->select('user_id')->where('group_id',(int)$req->leader_id)->pluck('user_id');

        if(count($members_id) <= 0){
            return response([
                'name'=>$user->name,
                'total'=>0,
                'valid'=>0,
                'unValid'=>0,
            ]);
        }
        $valid_array = User::select('valid')->whereIn('id',$members_id)->pluck('valid')->toArray();
        $result = array_count_values($valid_array);

        return response([
            'name'=>$user->name,
            'total'=>count($valid_array),
            'valid'=>(isset($result["1"]))?$result["1"]:0,
            'unValid'=>(isset($result["0"]))?$result["0"]:0,
        ]);
    }

}
