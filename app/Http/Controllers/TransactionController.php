<?php

namespace App\Http\Controllers;

use App\Jobs\SendMoney;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function __construct()
    {
        $this->middleware(['JWT','admin'],['only'=>['list','history','sendMoneyTo','sendMoneyToUsers','reserseTransaction']]);
        $this->middleware(['JWT'],['only'=>['myTransactionHistory']]);
    }

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

    /**
     * 送錢給單一使用者
     */
    public function sendMoneyTo(Request $request){

        $this->validate($request,[
            'id_code' => 'required',
            'event' => 'required',
            'amount' =>'required|integer|min:1',
        ]);

        if(!$user = User::where('id_code',$request->id_code)->first()){
            return response()->json([
                's'=>0,
                'm'=>'無此使用者'
            ]);
        }

        $user->update_wallet_with_trans(User::INCREASE_WALLET,$request->amount,$request->event);

        return response()->json([
            's'=>1,
            'm'=>'發送成功'
        ]);

    }

    /**
     * 送錢給多使用者
     */
    public function sendMoneyToUsers(Request $request){
        
        $this->validate($request,[
            'account_array' => 'required',
            'event' => 'required',
            'amount' =>'required|integer|min:1',
        ]);

        $max_row = 20;
        $account_array = json_decode($request->account_array,true);
        if(empty($account_array)){return response(['s'=>0,'m'=>'error']);}
        if(count($account_array) == 0){return response(['s'=>0,'m'=>'error']);}
        if(count($account_array) > $max_row){return response(['s'=>0,'m'=>'一次最多20筆資料']);}

        $not_found_array = [];
        foreach ($account_array as $account) {
            if(!$user = User::where('email',$account)->first()){
                $not_found_array[] = $account;
                continue;
            }
            $user->update_wallet_with_trans(User::INCREASE_WALLET,$request->amount,$request->event);
        }
        
        return response([
            's'=>1,
            'm'=>'success',
            'not_found_array'=>$not_found_array
        ]);

    }

    public function reserseTransaction(Request $request){
        
        $this->validate($request,[
            'tran_id' => 'required',
        ]);

        $transaction = Transaction::where('tran_id',$request->tran_id)->first();
        try {
            $transaction->reverse();
        } catch (\Throwable $th) {
            return response($th);
        }

        return response('success');
    }

    public function sendMoney(Request $request){

        // $this->validate($request,[
            // 'from' => 'required|integer',
            // 'to' => 'required|integer',
            // 'event' => 'required',
            // 'amount' =>'required|integer|min:1',
        // ]);

        
        $sendArray = [
            
        ];

        
        foreach ($sendArray as $send) {

            $email = $send[0];
            $amount = $send[1];
            $event = $send[2];

            $user = User::where('email',$email)->first();
            if(!$user){ continue; }

            $this->dispatch(new SendMoney($user,$amount,$event));
        }

        
        // $users = User::where('id','>=',$request->from)->where('id','<=',$request->to)->where('valid',1)->get();
        // foreach ($users as $user) {
        //     $this->dispatch(new SendMoney($user,$amount,$event));
        // }
        
        return response('success');
    }


    
    public function list(Request $request)
    {
        $page = ($request->page)?$request->page:1;
        $rows = ($request->rowsPerPage)?$request->rowsPerPage:15;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';
        if ($request->descending == null || $request->descending == 'false') {
            $ascOrdesc = 'asc';
        }

        $total = Transaction::count();
        $tranList = Transaction::skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get();

        $user_id_array = [];
        foreach ($tranList as $tran) { 
            if(!in_array($tran->user_id,$user_id_array)){
                $user_id_array[] = $tran->user_id;
            }
            if($tran->target_id != 0){
                if(!in_array($tran->target_id,$user_id_array)){
                    $user_id_array[] = $tran->target_id;
                }
            }
        }

        $users = User::whereIn('id',$user_id_array)->get();
        $nameDict = [];
        foreach ($users as $user) { $nameDict[$user->id] = $user->name; }

        return response([
            'total'=>$total,
            'tranList'=>$tranList,
            'nameDict'=>$nameDict
        ]);
        
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
        $trans = Transaction::where('user_id',$user_id)->orderBy('id','desc')->take(20)->get();
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
     * app 使用者查詢自己交易紀錄(app新版本取代 show)
     */
    public function myTransactionHistory(Request $request){

        $page = ($request->page)?$request->page:1;
        $rows = 10;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';

        $user = auth()->user();
        $total = Transaction::where('user_id',$user->id)->count();
        $_transList = Transaction::where('user_id',$user->id)->skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get();

        $user_id_array = [];
        foreach ($_transList as $tran) { 
            if($tran->target_id == 0){continue;}
            if(!in_array($tran->target_id,$user_id_array)){
                $user_id_array[] = $tran->target_id;
            }    
        }

        $users = User::whereIn('id',$user_id_array)->get();
        $nameDict = [];
        foreach ($users as $user) { $nameDict[$user->id] = $user->name; }

        $transList = [];
        foreach ($_transList as $tran) { 
            if($tran->target_id == 0){
                $tran['target_name'] = '銀髮學院';
            }else{
                $tran['target_name'] = (isset($nameDict[$tran->target_id]))?$nameDict[$tran->target_id]:'';
            }
            $transList[] = $tran;
        }

        $hasNextPage = true;
        if(($skip + $rows) >= $total){ $hasNextPage = false; }

        return response([
            'transList'=>$transList,
            'hasNextPage'=>$hasNextPage
        ]);

    }

    /**
     * 後台使用的api 查詢使用者交易紀錄
     */
    public function history(Request $request,$user_id){
        
        $page = ($request->page)?$request->page:1;
        $rows = 20;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';
        if ($request->descending == null || $request->descending == 'false') {
            $ascOrdesc = 'asc';
        }

        $total = Transaction::where('user_id',$user_id)->count();
        $transList = Transaction::where('user_id',$user_id)->skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get();

        return response([
            'total'=>$total,
            'transList'=>$transList
        ]);

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
