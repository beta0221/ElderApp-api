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

        $emailArray = [
            'H221415832',
            'J100244510',
            'H220676224',
            'K120560650',
            'H200273003',
            'J220408343',
            'H200280179',
            'H202045301',
            'H120395099',
            'K120150430',
            'H221562247',
            'H100435314',
            'U220642942',
            'H220780134',
            'J201688625',
            'J220515987',
            'H221190616',
            'H200451141',
            'K220337448',
            'J220930868',
            'H201419790',
            'K201699352',
            'K101327653',
            'H220847492',
            'J121441322',
            'J20201275',
            'H101436815',
            'H201343924',
            'V220153441',
            'J220656509',
            'J201189127',
            'M201359416',
            'G220466889',
            'H201224273',
            'H200252826',
            'H102552014',
            'H220578545',
            'H201399560',
            'F220055643',
            'H100344792',
            'Q200315708',
            'T200625775',
            'Q202001529',
            'H201336321',
            'H220512976',
            'H201020131',
            'J201380606',
            'R221502186',
            'H120528507',
            'H101632460',
            'H201556467',
            'J221306839',
            'J202276272',
            'K220449265',
            'U220748743',
            'U220701982',
            'R220228001',
            'U220525446',
            'H20041606',
            'H221034239',
            'H221250304',
            'J202389274',
        ];
        $event = $request->event;
        $amount = $request->amount;
        // $from = $request->from;
        // $to = $request->to;
        foreach ($emailArray as $email) {
            if($user = User::where('email',$email)->first()){
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
