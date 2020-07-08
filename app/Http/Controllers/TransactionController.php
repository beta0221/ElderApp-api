<?php

namespace App\Http\Controllers;

use App\Jobs\SendMoney;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{

    public function __construct()
    {
        $this->middleware(['JWT','admin'],['only'=>['list']]);
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

        $user->updateWallet(User::INCREASE_WALLET,$request->amount);

        Transaction::create([
            'tran_id'=>time() . rand(10,99),
            'user_id'=>$user->id,
            'event' =>$request->event,
            'amount'=>$request->amount,
            'target_id'=>0,
            'give_take'=>User::INCREASE_WALLET,
        ]);

        return response()->json([
            's'=>1,
            'm'=>'發送成功'
        ]);

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

        return response([
            'total'=>$total,
            'tranList'=>$tranList,
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
