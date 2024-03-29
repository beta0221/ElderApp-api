<?php

namespace App\Http\Controllers;

use App\Clinic;
use App\ClinicUserLog;
use App\Exports\ClinicBillExport;
use App\Exports\ClinicUserSignatureExport;
use App\Helpers\Pagination;
use App\Helpers\Tracker;
use App\Http\Resources\ClinicUserLogCollection;
use App\Jobs\NotifyAppUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

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
        
        $user = User::where('email',$request->id_code)->first();
        if(!$user){
            $user = User::where('id_code',$request->id_code)->first();
        }

        if(!$user){
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

        $created_at = date('Y-m-d h:i:s',strtotime("$request->date $request->from:00:00"));
        $complete_at = date('Y-m-d h:i:s',strtotime($created_at)+(60*60*$request->total_hours));

        $clinic->userLogs()->create([
            'user_id'=>$user->id,
            'created_at' => $created_at,
            'complete_at' => $complete_at,
            'total_hours' => $request->total_hours,
            'is_complete' => 1
        ]);

        $user->update_wallet_with_trans(User::INCREASE_WALLET,100,"志工獎勵-".$clinic->name);
        $user->increaseRank(1);
        NotifyAppUser::dispatch($user->id,'恭喜您！','收到志工獎勵，同時也累計了志工時數。');

        Session::flash('success','完成志工服務。');
        return redirect()->route('manageClinic',['slug'=>$slug]);
    }

    /**編輯志工時數 */
    public function updateLog(Request $request,$slug){
        $log = ClinicUserLog::findOrFail($request->id);
        $clinic = Clinic::where('slug',$slug)->firstOrFail();

        if($log->clinic_id != $clinic->id){
            return redirect()->route('manageVolunteersLogs',['slug'=>$slug]);
        }

        if($request->has('total_hours')){
            $log->update([
                'total_hours' => $request->total_hours
            ]);
        }
        return redirect()->route('manageVolunteersLogs',['slug'=>$slug]);
    }

    /**掃描志工QR Code 簽到 */
    public function api_scanQRCode_onDuty(Request $request,$slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $user = $request->user();

        if(!$clinic->users()->where('user_id',$user->id)->first()){
            return response('您不在此診所的志工名單。',403);
        }

        if($log = $clinic->userLogs()->where('user_id',$user->id)->where('is_complete',0)->first()){
            $log->delete();
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

        date_default_timezone_set('Asia/Taipei');
        $now = date('Y-m-d H:i:s');

        if(!$log = $clinic->userLogs()->orderBy('id','desc')->where('user_id',$user->id)->where('is_complete',0)->whereDate('created_at',date('Y-m-d'))->first()){
            return response('請先進行簽到',403);
        }

        $from = strtotime($log->created_at->format('Y-m-d H:i:s'));
        $to = strtotime($now);
        $total_seconds = $to - $from;
        $total_minutes = $total_seconds / 60;
        $total_hours = floor(($total_minutes + 10 ) / 60);  //十分鐘緩衝時間

        if($total_hours > 4){   //時數過長無效
            return response('時數過長，請重新簽到',403);
        }

        if($total_hours < 1){   //時數過短無效
            return response('未達最低時數，無法簽退',403);
        }

        $log->update([
            'is_complete'=>1,
            'complete_at'=>$now,
            'total_hours'=>$total_hours
        ]);

        $user->update_wallet_with_trans(User::INCREASE_WALLET,100,"志工獎勵-".$clinic->name);//使用者加錢
        $user->increaseRank(1);
        NotifyAppUser::dispatch($user->id,'恭喜您！','收到志工獎勵，同時也累計了志工時數。');

        return response('成功簽退');
    }

    /**後端輔助Android解碼 */
    public function api_decode(Request $request){
        return response(base64_decode($request->text));
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
        $query = $user->clinicLogs()->orderBy('id','desc');
        
        $sum_total_hours = $query->sum('total_hours');
        $logs = $query->paginate(10);
        
        $links = $logs->links();
        $logs = new ClinicUserLogCollection($logs);

        return view('clinic.myLogs',[
            'logs' => $logs->toArray($request),
            'links' => $links,
            'sum_total_hours' => $sum_total_hours
        ]);
    }

    /**診所的志工紀錄頁面 */
    public function view_volunteersLogs(Request $request,$slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $query = $clinic->userLogs()->orderBy('id','desc');

        if($request->has('user_name')){
            $user_id_array = User::where('name','LIKE',"%$request->user_name%")->pluck('id');
            $query->whereIn('user_id',$user_id_array);
        }

        $logs = $query->paginate(10);

        $links = $logs->appends($request->all())->links();
        $logs = new ClinicUserLogCollection($logs);

        return view('clinic.logs',[
            'clinic' => $clinic,
            'logs' => $logs->toArray($request),
            'links' => $links
        ]);
    }

    /** excel 輸出志工千名表頁面 */
    public function view_exportSignature(Request $request){
        $clinic = Clinic::where('slug',$request->clinic_slug)->firstOrFail();
        $logs = ClinicUserLog::where('clinic_id',$clinic->id)
            ->where('is_complete',1)
            ->whereDate('created_at','>=',date($request->from_date))
            ->whereDate('created_at','<=',date($request->to_date))
            ->get();
        
        $title = '結算日期：' . $request->from_date . '~' . $request->to_date;
        $excel = new ClinicUserSignatureExport($logs,$title);
        return Excel::download($excel,"$clinic->name-志工簽名表" . '.xlsx');
        return response($logs);
    }


    /** excel 輸出診所對帳表頁面 */
    public function view_exportClinicBill(Request $request){
        $clinic = Clinic::where('slug',$request->clinic_slug)->firstOrFail();
        $logs = ClinicUserLog::where('clinic_id',$clinic->id)
            ->where('is_complete',1)
            ->whereDate('created_at','>=',date($request->from_date))
            ->whereDate('created_at','<=',date($request->to_date))
            ->get();
        
        $title = '結算日期：' . $request->from_date . '~' . $request->to_date;
        $excel = new ClinicBillExport($logs,$title);
        return Excel::download($excel,"$clinic->name-診所對帳" . '.xlsx');
        return response($logs);
    }

    /** 全部診所列表 */
    public function api_allClinic(){
        return response(Clinic::all());
    }

    /** 志工服務列表 */
    public function api_allLog(Request $request){
        $p = new Pagination($request);
        $query = ClinicUserLog::orderBy($p->orderBy,$p->ascOrdesc);

        if($request->has('user_name')){
            $user_id_array = User::where('name','LIKE',"%$request->user_name%")->pluck('id');
            $query->whereIn('user_id',$user_id_array);
        }

        if($request->has('clinic_name')){
            $clinic_id_array = Clinic::where('name','LIKE',"%$request->clinic_name%")->pluck('id');
            $query->whereIn('clinic_id',$clinic_id_array);
        }

        $total = $query->count();
        $items =  $query->skip($p->skip)->take($p->rows)->get();
        $items = new ClinicUserLogCollection($items);
        
        return response([
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function api_userLog(Request $request,$user_id){
        $user = User::findOrFail($user_id);

        $p = new Pagination($request);
        $query = $user->clinicLogs()->orderBy($p->orderBy,$p->ascOrdesc);

        $total = $query->count();
        $logs = $query->skip($p->skip)->take($p->rows)->get();

        $logs = new ClinicUserLogCollection($logs);

        return response([
            'total'=>$total,
            'logs'=>$logs,
            'user'=>$user
        ]);
    }

    public function api_clinicLog(Request $request,$clinic_id){
        $clinic = Clinic::findOrFail($clinic_id);

        $p = new Pagination($request);
        $query = $clinic->userLogs()->orderBy($p->orderBy,$p->ascOrdesc);

        $total = $query->count();
        $logs = $query->skip($p->skip)->take($p->rows)->get();

        $logs = new ClinicUserLogCollection($logs);

        return response([
            'total'=>$total,
            'logs'=>$logs,
            'clinic'=>$clinic
        ]);
    }


}
