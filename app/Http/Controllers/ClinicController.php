<?php

namespace App\Http\Controllers;

use App\Clinic;
use App\ClinicUserLog;
use App\Helpers\Pagination;
use App\Helpers\Tracker;
use App\Http\Resources\ClinicUserLogCollection;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class ClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $p = new Pagination($request);
        $query = Clinic::orderBy($p->orderBy,$p->ascOrdesc);

        $total = $query->count();
        $clinicList = $query->skip($p->skip)->take($p->rows)->get();

        return response([
            'items' => $clinicList,
            'total' => $total,
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request,[
            'name'=>'required',
            'address'=>'required',
        ]);
        $request->merge(['slug'=>'C' . time()]);

        $clinic = Clinic::create($request->all());
        return response($clinic);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function show(Clinic $clinic)
    {
        return response($clinic);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function edit(Clinic $clinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Clinic $clinic)
    {
        $clinic->update($request->only([
            'name',
            'address',
            'link',
            'description',
        ]));

        return response($clinic);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function destroy(Clinic $clinic)
    {
        //
    }


    /**診所管理員 */
    public function getManagers($slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $managers = $clinic->managers()->get();
        return response($managers);
    }

    /**新增診所管理員 */
    public function addManager(Request $request,$slug){
        Tracker::log($request);
        
        $user = User::findOrFail($request->user_id);
        $clinic = Clinic::where('slug',$slug)->firstOrFail();

        if(!$result = $user->becomeRole('clinic_manager')){
            return response('error',400);
        }

        if(!$manager = $clinic->managers()->find($user->id)){
            $clinic->managers()->attach($user->id);
        }

        return response('success');
    }

    /**移除診所管理員 */
    public function removeManager(Request $request,$slug){
        Tracker::log($request);
        
        $user = User::findOrFail($request->user_id);
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        
        $clinic->managers()->detach($user->id);
        if(count($user->managerClinics()->get()) == 0){
            $user->removeRole('clinic_manager');
        }

        return response('success');
    }


    /**診所志工*/
    public function getUsers($slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $users = $clinic->users()->get();
        return response($users);
    }

    /**新增診所志工 */
    public function addUser(Request $request,$slug){
        Tracker::log($request);
        
        if(!$user = User::where('id_code',$request->id_code)->first()){
            Session::flash('error','會員編號不存在。');
            Session::flash('id_code',$request->id_code);
            return redirect()->route('manageClinic',['slug'=>$slug]);    
        }
        $clinic = Clinic::where('slug',$slug)->firstOrFail();

        if($clinicUser = $clinic->users()->find($user->id)){
            Session::flash('success','志工已存在。');
            return redirect()->route('manageClinic',['slug'=>$slug]);
        }

        $clinic->users()->attach($user->id);
        Session::flash('success','成功加入。');

        return redirect()->route('manageClinic',['slug'=>$slug]);    
    }

    /**移除診所志工 */
    public function removeUser(Request $request,$slug){
        Tracker::log($request);
        
        $user = User::where('id_code',$request->id_code)->firstOrFail();
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $clinic->users()->detach($user->id);
        Session::flash('success','成功移除。');

        return redirect()->route('manageClinic',['slug'=>$slug]);
    }

    /**完成志工服務 */
    public function doneVolunteering(Request $request,$slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $user = User::where('id_code',$request->id_code)->firstOrFail();
        date_default_timezone_set('Asia/Taipei');

        $clinic->userLogs()->create([
            'user_id'=>$user->id,
        ]);

        Session::flash('success','完成志工服務。');
        return redirect()->route('manageClinic',['slug'=>$slug]);
    }

    /**掃描志工QR Code 簽到 */
    public function api_scanQRCode_onDuty(Request $request,$slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $user = $request->user();

        if(!$clinic->users()->where('user_id',$user->id)->first()){
            return response('您不在此診所的志工名單。',403);
        }

        date_default_timezone_set('Asia/Taipei');
        $clinic->userLogs()->create([
            'user_id'=>$user->id,
        ]);

        return response('成功簽到');
    }

    
    /**掃描志工QR Code 簽退 */
    public function api_scanQRCode_offDuty(Request $request,$slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $user = $request->user();

        if(!$clinic->users()->where('user_id',$user->id)->first()){
            return response('您不在此診所的志工名單。',403);
        }

        if(!$log = $clinic->userLogs()->where('user_id',$user->id)->where('is_complete',0)->first()){
            return response('請先進行簽到',403);
        }

        date_default_timezone_set('Asia/Taipei');
        $log->update([
            'is_complete'=>1,
            'complete_at'=>date('Y-m-d H:i:s')
        ]);

        return response('成功簽退');
    }


    /**診所管理員的診所列表 */
    public function view_allClinic(Request $request){
        if($request->has('token')){
            Cookie::queue('token',$request->token,60);
        }

        $user = $request->user();
        $clinicList = $user->managerClinics()->get();
        
        return view('clinic.all',[
            'clinicList' => $clinicList
        ]);

    }

    /**診所管理時段介面 */
    public function view_manageClinic(Request $request,$slug){

        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $users = $clinic->users()->get();

        return view('clinic.manage',[
            'clinic' => $clinic,
            'users' => $users,
        ]);
    }

    /**診所列印qrcode頁面 */
    public function view_QRCode(Request $request,$slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();

        return view('clinic.QRCode',[
            'clinic' => $clinic,
            'onDutyQRCodeString' => $clinic->onDutyQRCodeString(),
            'offDutyQRCodeString' => $clinic->offDutyQRCodeString(),
        ]);
    }


    /**使用者的志工紀錄頁面 */
    public function view_volunteerLog(Request $request){
        $user = $request->user();
        $logs = $user->clinicLogs()->paginate(10);

        return view('clinic.logs',[
            'user' => $user,
            'logs' => $logs
        ]);
    }


    /** 志工服務列表 */
    public function api_clinicUserLog(Request $request){
        $p = new Pagination($request);
        $query = ClinicUserLog::orderBy($p->orderBy,$p->ascOrdesc);

        if($request->has('user_name')){
            $user_id_array = User::where('name','LIKE',"%$request->user_name%")->pluck('id');
            $query->whereIn('user_id',$user_id_array);
        }

        $total = $query->count();
        $items =  $query->skip($p->skip)->take($p->rows)->get();
        $items = new ClinicUserLogCollection($items);
        
        return response([
            'items' => $items,
            'total' => $total,
        ]);
    }


}
