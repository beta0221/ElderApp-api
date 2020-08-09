<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

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
            sleep(0.5);
        }
        return response($ageDist);
    }


}
