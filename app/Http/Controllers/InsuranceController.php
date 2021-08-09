<?php

namespace App\Http\Controllers;

use App\Helpers\Pagination;
use App\Insurance;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{

    /**後台index */
    public function index(Request $request){
        $p = new Pagination($request);
        $query = Insurance::orderBy($p->orderBy,$p->ascOrdesc);

        if($request->has('status')){
            $query->where('status',$request->status);
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
        return response($insurance);
    }

    /** 狀態進入下階段 */
    public function nextStatus(Request $request){
        $isAccountant = $request->user()->hasRole('accountant');
        $insuranceList = Insurance::whereIn('id',$request->id_array)->get();
        
        foreach ($insuranceList as $insurance) {
            if($insurance->status == Insurance::STATUS_VERIFIED){
                continue;
            }else if($insurance->status == Insurance::STATUS_PROCESSING && $isAccountant == false){
                continue;
            }
            $insurance->nextStatus();
        }

        return response('success');
    }

    /**保險生效 */
    public function issue(Request $request){
        
        if(!$request->has('issueDate')){ return response('error',500); }
        $issueDate = date('Y-m-d',strtotime($request->issueDate));
        $insuranceList = Insurance::whereIn('id',$request->id_array)->get();

        foreach ($insuranceList as $insurance) {
            if($insurance->status != Insurance::STATUS_VERIFIED){ continue; }
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
        ]);
        
        $user = $request->user();
        $user->insurances()->create([
            'name'=>$request->name,
            'identity_number'=>$request->identityNumber,
            'phone'=>$request->phone,
            'birthdate'=>$request->birthdate,
        ]);

        return response(['s'=>1, 'm'=>'success']);
    }



}