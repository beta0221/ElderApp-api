<?php

namespace App\Http\Controllers;

use App\Promocode;
use App\User;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromocodeController extends Controller
{
    public function coupon_generate($quantity,$amount){
        
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

        for($j=0; $j<$quantity;$j++){
            $res = "";
            for ($i = 0; $i < 10; $i++) {
                $res .= $chars[mt_rand(0, strlen($chars)-1)];
            }
            try {
                Promocode::create([
                    'promocode'=>$res,
                    'amount'=>$amount,
                ]);
            } catch (\Throwable $th) {
                return response($th);
            }
            
        }

        return response('success');
    }

    public function exchange(Request $request){
        
        $promocode = Promocode::where(DB::raw('BINARY `promocode`'),$request->promocode)->first();
        
        if(!$promocode){
            return response()->json([
                's'=>0,
                'm'=>'折扣碼不存在'
            ]);
        }

        if(!$promocode->available){
            return response()->json([
                's'=>0,
                'm'=>'此組折扣碼已被使用',
            ]);
        }

        //使用者加錢
        $user = User::find($request->user_id);
        if($user){
            $user->updateWallet(true,$promocode->amount);
        }else{
            return response('error');
        }

        //註銷兌換卷
        $promocode->available = 0;
        $promocode->save();


        //新增交易紀錄
        try {
            Transaction::create([
                'tran_id'=>$tran_id = time() . rand(10,99),
                'user_id'=>$request->user_id,
                'event' =>'樂幣兌換卷',
                'amount'=>$promocode->amount,
                'target_id'=>0,
                'give_take'=>true,
            ]);
        } catch (\Throwable $th) {
            return response($th);
        }
        
        


        return response()->json([
            's'=>1,
            'm'=>'成功領取。'
        ]);
    }





}
