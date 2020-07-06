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
            ['X220098838',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['K200815049',1500,'推薦5人獎勵點數(1/1~7/6)'],
            ['J200265379',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['B201191942',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['0919234186',6600,'推薦22人獎勵點數(1/1~7/6)'],
            ['H201409678',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['H202039134',3000,'推薦10人獎勵點數(1/1~7/6)'],
            ['J202242785',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['H200273003',600,'推薦2人獎勵點數(1/1~7/6)'],
            ['H201418766',2100,'推薦7人獎勵點數(1/1~7/6)'],
            ['H221593706',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H201562705',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H221753319',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H121050115',600,'推薦2人獎勵點數(1/1~7/6)'],
            ['F202343597',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H200406619',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['A200794984',600,'推薦2人獎勵點數(1/1~7/6)'],
            ['H201563766',1800,'推薦6人獎勵點數(1/1~7/6)'],
            ['H101632460',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H201556467',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H200310074',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['H202021276',2400,'推薦8人獎勵點數(1/1~7/6)'],
            ['J200986417',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['H200310387',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['H201288302',5400,'推薦18人獎勵點數(1/1~7/6)'],
            ['H221470756',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['C200782762',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['K100435281',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['L100242455',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H202027572',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['H220808315',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['J121403368',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['J102241464',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['H221660417',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['R200333805',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['julie9066@gmail.com',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H202189835',2700,'推薦9人獎勵點數(1/1~7/6)'],
            ['K220337448',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['J200862645',900,'推薦3人獎勵點數(1/1~7/6)'],
            ['F222195060',600,'推薦2人獎勵點數(1/1~7/6)'],
            ['H220426224',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['H220676224',600,'推薦2人獎勵點數(1/1~7/6)'],
            ['M220671437',300,'推薦1人獎勵點數(1/1~7/6)'],
            ['V200653913',600,'推薦2人獎勵點數(1/1~7/6)'],
            ['H200273003',400,'小天使組內新加入2位會員獎勵(1/1~7/6)'],
            ['Y100025712',200,'小天使組內新加入1位會員獎勵(1/1~7/6)'],
            ['H101632460',200,'小天使組內新加入1位會員獎勵(1/1~7/6)'],
            ['H201556467',600,'小天使組內新加入3位會員獎勵(1/1~7/6)'],
            ['J201688625',200,'小天使組內新加入1位會員獎勵(1/1~7/6)'],
            ['K220337448',200,'小天使組內新加入1位會員獎勵(1/1~7/6)'],
            ['K101698451',800,'小天使組內新加入4位會員獎勵(1/1~7/6)'],
            ['J200862645',600,'小天使組內新加入3位會員獎勵(1/1~7/6)'],
            ['F222195060',400,'小天使組內新加入2位會員獎勵(1/1~7/6)'],
            ['H220676224',600,'小天使組內新加入3位會員獎勵(1/1~7/6)'],
            ['M220671437',200,'大天使組內新加入2位會員獎勵(1/1~7/6)'],
            ['H200310074',1200,'大天使組內新加入12位會員獎勵(1/1~7/6)'],
            ['H201288302',2200,'大天使組內新加入22位會員獎勵(1/1~7/6)'],
            ['K200815049',500,'大天使組內新加入5位會員獎勵(1/1~7/6)'],
            ['J200265379',100,'大天使組內新加入1位會員獎勵(1/1~7/6)'],
            ['Y200194723',100,'大天使組內新加入1位會員獎勵(1/1~7/6)'],
            ['0919234186',2200,'大天使組內新加入22位會員獎勵(1/1~7/6)'],
            ['H201409678',300,'大天使組內新加入3位會員獎勵(1/1~7/6)'],
            ['H202039134',1000,'大天使組內新加入10位會員獎勵(1/1~7/6)'],
            ['J202242785',300,'大天使組內新加入3位會員獎勵(1/1~7/6)'],
            ['H220677203',500,'大天使組內新加入5位會員獎勵(1/1~7/6)'],
            ['H201418766',900,'大天使組內新加入9位會員獎勵(1/1~7/6)'],
            ['J201646547',700,'大天使組內新加入7位會員獎勵(1/1~7/6)'],
            ['A200794984',200,'大天使組內新加入2位會員獎勵(1/1~7/6)'],
            ['H201563766',600,'大天使組內新加入6位會員獎勵(1/1~7/6)'],
            ['H202021276',800,'大天使組內新加入8位會員獎勵(1/1~7/6)'],
            ['J200986417',300,'大天使組內新加入3位會員獎勵(1/1~7/6)'],
            ['H200310387',300,'大天使組內新加入3位會員獎勵(1/1~7/6)'],
            ['H221470756',100,'大天使組內新加入1位會員獎勵(1/1~7/6)'],
            ['C200782762',1500,'大天使組內新加入15位會員獎勵(1/1~7/6)'],
            ['H221660417',300,'大天使組內新加入3位會員獎勵(1/1~7/6)'],
            ['R200333805',200,'大天使組內新加入2位會員獎勵(1/1~7/6)'],
            ['H202189835',900,'大天使組內新加入9位會員獎勵(1/1~7/6)'],
            ['H201223114',400,'大天使組內新加入4位會員獎勵(1/1~7/6)'],
            ['V200653913',200,'大天使組內新加入2位會員獎勵(1/1~7/6)'],
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
