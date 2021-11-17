<?php

namespace App\Http\Controllers;

use App\Helpers\Pagination;
use App\Helpers\Tracker;
use App\Http\Resources\TransCollection;
use App\Jobs\NotifyAppUser;
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
            return response('無效操作',400);
        }

        $give_user = User::find($req->give_id);
        $take_user = User::find($req->take_id);

        if ($give_user->email != $req->give_email || $take_user->email != $req->take_email) {
            return response('data uncorrect',400);
        }

        if($give_user->wallet < $req->amount){ return response('insufficient',400); }

        if($give_user->hasRole('partner_store')){
            return response('無效操作',400);
        }

        $message = "收到來自" . $give_user->name ."的樂幣" . $req->amount . "點";
        $title = "來自好友的祝福";
        $data = null;

        //如果是特約交易
        if($take_user->hasRole('partner_store')){
            $title = "入帳通知";
            $data = [
                'updateWallet'=>'true',
                'message'=>$message,
                'actionUrl'=>'/transaction/myTransactionHistory'
            ];
        }

        $give_user->update_wallet_with_trans(User::DECREASE_WALLET,$req->amount,$req->event,$take_user->id);
        $take_user->update_wallet_with_trans(User::INCREASE_WALLET,$req->amount,$req->event,$give_user->id);
            
        NotifyAppUser::dispatch($take_user->id,$title,$message,$data);
        return response('success',200);
        
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

        Tracker::log($request);
        
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

        if(!$user->isSameAssociation()){
            return response('錯誤操作', 403);
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
        Tracker::log($request);
        
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
        Tracker::log($request);
        
        $this->validate($request,[
            'tran_id' => 'required',
        ]);

        $transaction = Transaction::findOrFail($request->tran_id);
        Tracker::info(json_encode($transaction));
        
        try {
            $transaction->reverse();
        } catch (\Throwable $th) {
            return response($th);
        }

        return response('success');
    }

    /**後台交易總列表 */
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
        
        $request->merge(['rowsPerPage'=>20]);
        $p = new Pagination($request);

        $query = Transaction::where('user_id',$user_id)->orderBy($p->orderBy,$p->ascOrdesc);

        if($request->has('queryEvent')){
            $query->where('event','LIKE',"%$request->queryEvent%");
        }

        $total = $query->count();
        $transList = $query->skip($p->skip)->take($p->rows)->get();

        return response([
            'total'=>$total,
            'transList'=>$transList
        ]);

    }

    /**
     * 夥伴商店使用 收款紀錄
     */
    public function view_parterStoreTransList(Request $request,$id_code){
        $user = User::where('id_code',$id_code)->firstOrFail();
        if(!$user->hasRole('partner_store')){
            abort(404);
        }
        $query = Transaction::where('user_id',$user->id)->orderBy('id','desc');

        $_transList = $query->paginate(10);
        $links = $_transList->links();

        $transList = new TransCollection($_transList);

        return view('partner.transList',[
            'user' => $user,
            'transList' => $transList->toArray(null),
            'links' => $links,
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
