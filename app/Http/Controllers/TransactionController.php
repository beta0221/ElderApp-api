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
            // 'month'=>'required|integer'
        // ]);

        $id_array = [
            'H201408948',
            'H201325819',
            'Q202682551',
            'P200299342',
            'H220916137',
            'H120686531',
            'A200794984',
            'Y100025712',
            'H201296484',
            'U220340505',
            'H221620226',
            'H220684075',
            'L221038685',
            'H220498588',
            'T121484138',
            'Q201957380',
            'K201467074',
            'G200223477',
            'H226246177',
            'W100251302',
            'H220049478',
            'H102267832',
            'E290043769',
            'H202021276',
            'H200251909',
            'H100446924',
            'H220402715',
            'J202209119',
            'H220362752',
            'H120152362',
            'H200908725',
            'J201688625',
            'N220774783',
            'M220931894',
            'V120152362',
            'H120152363',
            'J101422787',
            'J201192700',
            'J101434032',
            'J220796115',
            'H201383393',
            'E200879253',
            'H200492035',
            'C100985314',
            'H200310074',
            'H101632460',
            'H201556476',
            'H102126827',
            'J202002549',
            'H102126587',
            'J201205117',
            'H220535559',
            'H220535540',
            'H201374983',
            'H201288302',
            'F102485598',
            'A203512877',
            'F232630030',
            'L221119971',
            'H202136638',
            'H201418766',
            'F104048266',
            'G101489244',
            'H201520412',
            'F203301637',
            'N102691289',
            'V220259171',
            'C200291677',
            'H201391644',
            'K200433716',
            'H221054124',
            'H120686246',
            'H200365631',
            'H220274328',
            'H101329477',
            'H201295816',
            'S202116447',
            'C200834912',
            'J101119418',
            'H221034239',
            'A200797592',
            'A210092620',
            'G220077104',
            'N203357215',
            'R201710495',
            'A223071466',
            'H102431690',
            'H201409481',
            'N222513635',
            'H220360422',
            'H101434624',
            'H100302356',
            'J102627651',
            'H201379488',
            'R200327754',
            'J201189127',
            'Q201175422',
            'V220153441',
            'J220729034',
            'E221822370',
            'H220677203',
            'H220676224',
            'L220760607',
            'H220426224',
            'K120150430',
            'K200866322',
            'G201316140',
            'DB20001570',
            'H220677212',
            'H221562247',
            'H101334503',
            'S201230251',
            'H200665243',
            'R221786833',
            'J200265379',
            'H220855878',
            'H200905395',
            'H100384938',
            'H201379415',
            'H202044761',
            'H220677203',
            'T120509770',
            'T200625775',
            'N122416219',
            'J220278667',
            'V200550382',
            'P201940231',
            'K200815049',
            'H101421010',
            'H101384656',
            'F203397777',
            'N221594172',
            'H201336321',
            'T102428325',
            'J220579052',
            'A210398261',
            'H221766576',
            'H220699923',
            'H220578545',
            'Q120879087',
            'K221058348',
            'H201223114',
            'H200451141',
            'J220930868',
            'J200862645',
            'H200272319',
            'B100382054',
            'A200767923',
            'F121301302',
            'K220118247',
            'H101382778',
            'H202301859',
            'S221870831',
            'J102344291',
            'A221213733',
            'F221600651',
            'H221385768',
            'H221739300',
            'J202242785',
            'K200648780',
            'H201224273',
            'J102033720',
            'H100286348',
            'H200252826',
            'H202172796',
            'H101312285',
            'H202176285',
            'H202188249',
            'K101327653',
            'H201378792',
            'H201378409',
            'J200839360',
            'H100297663',
            'H220677375',
            'U200963119',
            'H100252226',
            'F202214055',
            'N200719937',
            'H220847492',
            'H200343993',
        ];

        $event = '活動獎勵-日月潭金針花園區一日遊';
        $amount = 100;
        
        
        foreach ($id_array as $id_number) {
            $user = User::where('email',$id_number)->first();
            
            if($user){
                if($user->id != 2574 || $user->id != 2243){
                    $this->dispatch(new SendMoney($user,$amount,$event));
                }
            }else{
                Log::channel('eventlog')->info('id_number '.$id_number.' not found');
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
