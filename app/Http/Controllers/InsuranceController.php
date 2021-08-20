<?php

namespace App\Http\Controllers;

use App\Exports\InsuranceExport;
use App\Helpers\Pagination;
use App\Insurance;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InsuranceController extends Controller
{

    /**後台index */
    public function index(Request $request){
        $p = new Pagination($request);
        $query = Insurance::orderBy($p->orderBy,$p->ascOrdesc);

        if($request->has('status')){
            $query->where('status',$request->status);
        }

        if($request->has('q_4')){
            $query->where('q_4',$request->q_4);
        }

        if($request->has('q_5')){
            $query->where('q_5',$request->q_5);
        }

        if($request->has('name')){
            $query->where('name','LIKE',"%$request->name%");
        }

        $total = $query->count();
        $insuranceList = $query->skip($p->skip)->take($p->rows)->get();

        return response([
            'items' => $insuranceList,
            'total' => $total,
        ]);
    }

    /** Insurance 詳細資料 */
    public function show($id,Request $request){
        $insurance = Insurance::findOrFail($id);
        $insurance->user;
        return response($insurance);
    }

    /** Insurance 更新 */
    public function update($id,Request $request){
        $insurance = Insurance::findOrFail($id);

        $insurance->update([
            'identity_number'=>$request->identity_number,
            'phone'=>$request->phone,
            'birthdate'=>$request->birthdate,
            'occupation'=>$request->occupation,
            'relation'=>$request->relation,
            'q_1'=>$request->q_1,
            'q_2'=>$request->q_2,
            'q_3'=>$request->q_3,
            'q_4'=>$request->q_4,
            'q_5'=>$request->q_5,
            'description'=>$request->description,
        ]);
        
        return response($insurance);
    }

    /** 狀態進入下階段 */
    public function nextStatus(Request $request){
        $isAccountant = $request->user()->hasRole('accountant');
        $insuranceList = Insurance::whereIn('id',$request->id_array)->get();
        
        foreach ($insuranceList as $insurance) {
            if($insurance->status == Insurance::STATUS_CLOSE && $isAccountant == false){
                continue;
            }else if($insurance->status != Insurance::STATUS_PENDING){
                continue;
            }
            $insurance->nextStatus();
        }

        return response('success');
    }

    /**保險生效 */
    public function issue(Request $request){
        
        if(!$request->has('issueDate')){ return response('error',500); }

        $issueDate = date('Y-m-d',strtotime('+1 years -1 day' ,strtotime($request->issueDate)));  //加一年
        
        $insuranceList = Insurance::whereIn('id',$request->id_array)->get();

        foreach ($insuranceList as $insurance) {
            if($insurance->status != Insurance::STATUS_PROCESSING){ continue; }
            $insurance->nextStatus();
            $insurance->issue($issueDate);
        }

        return response('success');
    }

    /**作廢 */
    public function void($id,Request $request){
        $insurance = Insurance::findOrFail($id);
        $insurance->void();
        return response('success');
    }

    /**App 使用者申請保險 */
    public function apply(Request $request){

        $this->validate($request,[
            'name'=>'required',
            'identityNumber'=>'required',
            'phone'=>'required',
            'birthdate'=>'required',
            'occupation'=>'required',
            'relation'=>'required',
            'q_1'=>'required',
            'q_2'=>'required',
            'q_3'=>'required',
            'q_4'=>'required',
            'q_5'=>'required',
            'agree'=>'required',
        ]);
        
        if($request->occupation == '其他'){}{
            $request->occupation = $request->occupation_other;
        }

        $user = $request->user();
        $user->insurances()->create([
            'name'=>$request->name,
            'identity_number'=>$request->identityNumber,
            'phone'=>$request->phone,
            'birthdate'=>$request->birthdate,
            'occupation'=>$request->occupation,
            'relation'=>$request->relation,
            'q_1'=>$request->q_1,
            'q_2'=>$request->q_2,
            'q_3'=>$request->q_3,
            'q_4'=>$request->q_4,
            'q_5'=>$request->q_5,
            'description'=>$request->description,
        ]);

        return view('insurance.success');
    }


    /**App 申請表頁面 */
    public function view_apply(Request $request){
        
        $user = $request->user();

        return view('insurance.apply',[
            'user'=>$user,
            'token'=>$request->token
        ]);
    }

    /**列印書面 */
    public function view_print(Request $request){

        $id_array = explode(',',$request->id_array);
        $insuranceList = Insurance::whereIn('id',$id_array)->get();

        return view('insurance.print',[
            'insuranceList'=>$insuranceList
        ]);
    }

    /**輸出csv */
    public function view_export(Request $request){
        $id_array = explode(',',$request->id_array);
        $insuranceList = Insurance::whereIn('id',$id_array)->get();
        $nameDict = User::getNameDictByIdArray($id_array);

        $export = new InsuranceExport($insuranceList,$nameDict);
        return Excel::download($export,'保險申請.csv');
    }


}
