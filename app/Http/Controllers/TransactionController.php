<?php

namespace App\Http\Controllers;

use App\Jobs\SendMoney;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function transaction(Request $req)
    {
        $this->validate($req,[
            'give_id' => 'required|integer',
            'give_email' => 'required',
            'take_id' => 'required|integer',
            'take_email' => 'required',
            'amount' =>'required|integer|min:1',
            // 'event' => 'required',
        ]);

        if($req->give_id == $req->take_id){
            return response('無效操作');
        }

        $give_user = User::find($req->give_id);
        $take_user = User::find($req->take_id);

        if ($give_user->email == $req->give_email && $take_user->email == $req->take_email) {
            
            if($give_user->wallet < $req->amount){
                return response('insufficient');
            }

            $give_user->updateWallet(false,$req->amount);
            $take_user->updateWallet(true,$req->amount);
            
            $store = $this->store($req);
            if ($store) {
                return response('success',200);
            }

            return response($store,400);




        }
        
        return response('data uncorrect',400);
        
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($req)
    {
        $tran_id = time() . rand(10,99);

        try {
            Transaction::create([
                'tran_id'=>$tran_id,
                'user_id'=>$req->give_id,
                'event' =>$req->event,
                'amount'=>$req->amount,
                'target_id'=>$req->take_id,
                'give_take'=>false,
            ]);
    
            Transaction::create([
                'tran_id'=>$tran_id,
                'user_id'=>$req->take_id,
                'event' =>$req->event,
                'amount'=>$req->amount,
                'target_id'=>$req->give_id,
                'give_take'=>true,
            ]);

        } catch (\Throwable $th) {
            return $th;
        }
        
        return true;
        
    }


    public function sendMoney(Request $request){

        $this->validate($request,[
            // 'from' => 'required|integer',
            // 'to' => 'required|integer',
            'event' => 'required',
            'amount' =>'required|integer|min:1',
        ]);

        $nameArray = [
            '藍徐碧珠',
            '彭鼎福',
            '楊妙枝',
            '何春美',
            '劉俐臨',
            '劉美玉',
            '吳明香',
            '葉王淑娟',
            '黃丞薇',
            '蔡雙英',
            '鍾雲珍',
            '李桂榮',
            '陳春蘭',
            '徐貴妹',
            '陳銘椒',
            '傅治靜',
            '王昭紋',
            '邱凡榮',
            '吳清龍',
            '黃桃妹',
            '張温秋英',
            '陳佩貞',
            '麥萱橞',
            '林明志',
            '岳黃早妹',
            '黃玉蓮',
            '羅許寶玉',
            '郭幼能',
            '張玉窓',
            '李春珠',
            '謝黃春妹',
            '林振發',
            '陳秋鈴',
            '馮淑娟',
            '陳麗文',
            '岳昌宏',
            '詹勛智',
            '邱漢水',
            '范光明',
            '許成',
            '張木生',
            '莊德清',
            '羅炎祥',
            '周淑容',
            '廖珍芬',
        ];
        $event = $request->event;
        $amount = $request->amount;
        // $from = $request->from;
        // $to = $request->to;
        foreach ($nameArray as $name) {
            $users = User::where('name',$name)->get();
            foreach ($users as $user) {
                $this->dispatch(new SendMoney($user,$amount,$event));
            }
        }
        
        return response('success');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    

    /**
     * Display the specified resource.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show($user_id)
    {
        $trans = Transaction::where('user_id',$user_id)->orderBy('id','desc')->get();
        $result = [];
        foreach($trans as $tran){
            if($tran->target_id == 0){
                $tran['target_name'] = '銀髮學院';
            }else{
                $target_name = User::find($tran->target_id)->name;
                $tran['target_name'] = $target_name;
            }
            array_push($result,$tran);
        }
        return response()->json($result);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
