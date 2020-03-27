<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Transaction;
use App\User;
use App\Category;
use App\FreqEventUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class EventController extends Controller
{
    public function __construct()
    {
        $this->middleware(['JWT','BCP'], ['only' => ['index','store','destroy','update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $page = $request->page;
        $rows = $request->rowsPerPage;
        $skip = ($page - 1) * $rows;
        if ($request->descending == null || $request->descending == 'false') {
            $ascOrdesc = 'asc';
        } else {
            $ascOrdesc = 'desc';
        }
        $orderBy = ($request->sortBy) ? $request->sortBy : 'id';

        $events = DB::table('events')
        ->select('*')
        ->orderBy($orderBy, $ascOrdesc)
        ->where(function($query){
            if(Auth::user()->hasRole('teacher')){
                $query->where('owner_id',Auth::user()->id);
            }
        })
        ->skip($skip)
        ->take($rows)
        ->get();

        $total = DB::table('events')->count();

        return response()->json([
            'events' => $events,
            'total' => $total,
        ]);

    }

    //手機
    public function getEvents(Request $request){
        $events = Event::where(function($query)use($request){

            if($request->category){
                $query->where('category_id',$request->category);
            }

            if($request->district){
                $query->where('district_id',$request->district);
            }

        })->orderBy('created_at','desc')->get();

        //加入人數 & 使用者是否加入
        
        foreach($events as $event){
            $numberOfPeople = $event->numberOfPeople();
            $event['numberOfPeople'] = $numberOfPeople;
        }


        return $events;
    }
 
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $this->validate($request,[
                'title'=>'required',
                'body'=>'required',
                'location'=>'required',
                'maximum'=>'required|min:1|integer',
                'category_id'=>'required',
                'district_id'=>'required',
                'file'=>'sometimes|nullable|image',
            ]);
        }catch(Exception $e){
            return response($e);
        }

        if(Auth::user()->hasRole('teacher')){
            $request['owner_id'] = Auth::user()->id;
        }

        $event_slug='A'.time();
        $request['slug']=$event_slug;
        


        switch ($request->event_type) {
            case Event::TYPE_FREQUENTLY:
                if(!isset($request->days) || $request->days < Event::MIN_DAYS){
                    return response()->json([
                        's'=>0,'m'=>'天數不可為空，且必須大於8天。'
                    ]);
                }
                break;
            case Event::TYPE_ONETIME;
                if(!$this->isEventDateTimeAllGood($request)){
                    return response()->json([
                        's'=>0,'m'=>'活動時間與截止期限設定錯誤!'
                    ]);
                }
                unset($request['days']);
                break;
            default:
                break;
        }
        
        if($request->hasFile('file')){
            if(!$filename = $this->imageHandler($request->file('file'),$event_slug)){
                return response()->json([
                    's'=>0,'m'=>'檔案無法儲存',
                ]);
            }
            $request->merge(['image'=>$filename]);   
        }else{
            $request->merge(['image'=>""]);
        }


        //--------------------------------------------------
        try {
            $event=Event::create($request->except('file'));
        } catch (\Throwable $th) {
            return response()->json(['s'=>0,'m'=>json_encode($th)]);
        }
        //--------------------------------------------------
        
        return response()->json([
            's'=>1,'event'=>$event,
        ]);
        
    }


    
    private function imageHandler($file,$event_slug){
        
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = "/images/events/" . $event_slug . "/";
        
        if(Storage::disk('local')->exists($path)){
            $result = Storage::deleteDirectory($path);
            if(!$result){
                return false;
            }
        }
        
        if(!Storage::disk('local')->put($path . $filename,File::get($file))){
            return false;//失敗:回傳false
        }
        return $filename;//成功：回傳檔名
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $event= Event::where('slug',$slug)->first();
        //-----------------------------------------------------------------是否有登入
       if($event){
        return response()->json([
            's'=>1,
            'event'=>$event,
        ]);
       }else{
            return response()->json([
                's'=>0,
                'm'=>'Event not found!'
            ]);
        }
    }

  
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        
        if(!$event=Event::where('slug',$slug)->first()){
            return response()->json([
                's'=>0,'m'=>'Event not found!'
            ]);
        }
        
        if(Auth::user()->hasRole('teacher')){
            if($event->owner_id != Auth::user()->id){
                return response()->json([
                    's'=>0,'m'=>'Event not found!'
                ]);
            }
        }

        switch ($request->event_type) {
            case Event::TYPE_FREQUENTLY:
                if(!isset($request->days) || $request->days < Event::MIN_DAYS){
                    return response()->json([
                        's'=>0,'m'=>'天數不可為空，且必須大於8天。'
                    ]);
                }
                unset($request['deadline']);
                unset($request['dateTime']);
                unset($request['dateTime_2']);
                break;
            case Event::TYPE_ONETIME;
                if(!$this->isEventDateTimeAllGood($request)){
                    return response()->json([
                        's'=>0,'m'=>'活動時間與截止期限設定錯誤!'
                    ]);
                }
                unset($request['days']);
                break;
            default:
                break;
        }

        
        if($request->hasFile('file')){
            if(!$filename = $this->imageHandler($request->file('file'),$event->slug )){
                return response()->json([
                    's'=>0,'m'=>'檔案無法儲存',
                ]);
            }
            $request->merge(['image'=>$filename]);
        }

        if($event->maximum > $request->maximum){
            unset($request['maximum']);
        }


        try {
            $event->update($request->except('file'));
        } catch (\Throwable $th) {
            return response($th,500);
        }            

        return response()->json([
            's'=>1,'m'=>'update success!'
        ]);
        
        
        
    }

    private function isEventDateTimeAllGood(Request $request){

        if(empty($request->deadline)){return false;}
        if(empty($request->dateTime)){return false;}
        if(empty($request->dateTime_2)){return false;}

        $unixNow=time();
        $unixDeadline=strtotime($request->deadline);
        $unixDateTime=strtotime($request->dateTime);
        if($unixNow>$unixDeadline || $unixNow>$unixDateTime || $unixDeadline>$unixDateTime)
        {
            return false;
        }
        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $event=Event::where('slug',$slug)->first();
        if($event){
            $event->delete();
            return response()->json([
                's'=>1,
                'm'=>'delete success!'
            ]);
        }else{
            return response()->json([
                's'=>0,
                'm'=>'Error Occured!'
            ]);
        }
        
    }

    public function MyEvent(Request $request)
    {
        $user = User::where('id',$request->id)->first();
        if($user){
            $my_event = $user->go_events()->get();
            return response()->json($my_event);
        }else{
            return response()->json([
                's'=>0,
                'm'=>'User not found!'
            ]);
        }
        
    }
    public function EventGuests($slug){
        $event=Event::where('slug',$slug)->first();
        
        if(!$event){
            return response()->json([
                's'=>0,
                'm'=>'Event not found!'
            ]);    
        }

        return response()->json([
            's'=>1,
            //select: id,name,img
            'guests'=>$event->guests()->get(),
        ]);

    }

    public function JoinEvent(Request $request,$slug){

        if(!$event=Event::where('slug',$slug)->first()){
            return response()->json([
                's'=>0,'m'=>'此活動不存在。'
            ]);
        }

        if($event->event_type == Event::TYPE_ONETIME){
            $unixDeadline=strtotime($event->deadline);
            $unixNow=time();
            if($unixNow>$unixDeadline){
                return response()->json([
                    's'=>0,
                    'm'=>'報名已截止!'
                ]);
            }
        }

        

        if($event->maximum <= $event->numberOfPeople()){
            return response()->json([
                's'=>0,
                'm'=>'此活動已達人數上限。'
            ]);
        }

        $user=User::where('id',$request->id)->first();

        if(!$user->valid){
            return response()->json([
                's'=>0,'m'=>'非常抱歉，活動報名僅限為"有效會員"，您為"待付費會員"無法報名參與。'
            ]);
        }



        if(!$user->go_events()->find($event->id)){
            Log::channel('eventlog')->info('user '.$user->id.' joine event '.$event->id);
            $user->go_events()->attach($event->id);
            $event->peopleIncrease();
            return response()->json([
                's'=>1,
                'm'=>'加入成功!'
            ]);
        }else{
            return response()->json([
                's'=>0,
                'm'=>'已經加入'
            ]);
        }
         
    }

    public function CancelEvent(Request $request,$slug){

        $event=Event::where('slug',$slug)->first();
        if($event){
            $user=User::where('id',$request->id)->first();
            if($user->go_events()->find($event->id)){
                Log::channel('eventlog')->info('user '.$user->id.' cancel event '.$event->id);
                $user->go_events()->detach($event->id);
                $event->peopleDecrease();
                return response()->json([
                    's'=>1,
                    'm'=>'成功取消參加活動'
                ]);
            }else{
                return response()->json([
                    's'=>0,
                    'm'=>'成功取消參加活動'
                ]);
            }
            
        }
        else{
            return response()->json([
                's'=>0,
                'm'=>'此活動不存在。'
            ]);
        }

    }
    public function which_category_event($name){
        $category=Category::where('name',$name)->first();
        if($category){
            $those_events=$category->events()->get();
            return response()->json($those_events);
        }else
        {
            return response()->json([
                's'=>0,
                'm'=>'Category not found!'
            ]);
        }
    }

    public function rewardQrCode($slug){
        if(!$event = Event::where('slug',$slug)->first()){
            return response('活動不存在');
        }
        switch ($event->event_type) {
            case Event::TYPE_FREQUENTLY:
                return view('event.days_qrcode',[
                    'event'=>$event,
                ]);
                break;
            case Event::TYPE_ONETIME:
                return view('event.qrcode',[
                    'event'=>$event,
                ]);    
                break;
            default:
                break;
        }
    }

    public function updateEventCurrentDay(Request $request,$slug){
        
        if(!$event = Event::where('slug',$slug)->first()){
            return response()->json([
                's'=>0,'m'=>'活動不存在'
            ]);
        }

        $upToDay = $request->upToDay;
        if(!isset($upToDay) || empty($upToDay)){
            return response()->json([
                's'=>0,'m'=>'upToDay cannot be null or empty'
            ]);
        }

        if($event->current_day >= $upToDay){
            return response()->json([
                's'=>0,'m'=>'not allow to go backward'
            ]);
        }

        
        try {
            $event->update([
                'current_day'=>$upToDay,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                's'=>0,'m'=>json_encode($th)
            ]);
        }

        return response()->json([
            's'=>1,'m'=>'success'
        ]);


    }
    
    public function isUserArrive($slug){
        $user_id = Auth::user()->id;

        $event = Event::where('slug',$slug)->first();
        if(!$event){
            return response()->json([
                's'=>0,
                'm'=>'Event not found!'
            ]);
        }

        if(!$event->isParticipated($user_id)){
            return response()->json([
                's'=>0,
                'm'=>'非常抱歉，您不在此活動的參加人員名單中'
            ]);
        }

        if($event->isArrived($user_id)){
            return response()->json([
                's'=>1,
                'm'=>'已完成報到'
            ]);
        }

        return response()->json([
            's'=>2,
            'm'=>'未完成報到'
        ]);


    }


    public function arriveEvent($slug){

        $user_id = Auth::user()->id;

        $event = Event::where('slug',$slug)->first();
        if(!$event){
            return response()->json([
                's'=>0,
                'm'=>'Event not found!'
            ]);
        }

        if(!$event->isParticipated($user_id)){
            return response()->json([
                's'=>0,
                'm'=>'非常抱歉，您不在此活動的參加人員名單中'
            ]);
        }

        if($event->isArrived($user_id)){
            return response()->json([
                's'=>1,
                'm'=>'已完成報到',
                'name'=>$event->title
            ]);
        }

        $event->arrive($user_id);
        return response()->json([
            's'=>1,
            'm'=>'已完成報到',
            'name'=>$event->title
        ]);


    }

    public function drawEventReward(Request $request,$slug){

        //兩個平台的參數不一，在這邊暫時統一，之後還是要使用 Auth
        $user_id = 0;
        if($request->id){
            $user_id = $request->id;
        }else if($request->user_id){
            $user_id = $request->user_id;
        }

        //修正舊版手機程式
        if(strpos($slug,',') !== false){
            $str = explode(',',$slug);
            $slug = $str[1];
        }

        if(!$event = Event::where('slug',$slug)->first()){
            return response()->json([
                's'=>0,'m'=>'Event not found!'
            ]);
        }
        
        if(!$event->isParticipated($user_id)){
            return response()->json([
                's'=>0,
                'm'=>'非常抱歉，您不在此活動的參加人員名單中'
            ]);
        }

        switch ($event->event_type) {
            case Event::TYPE_FREQUENTLY:
                
                if(!$current_day = $event->current_day){
                    return response()->json([
                        's'=>0,'m'=>'活動尚未開始。'
                    ]);
                }

                if(FreqEventUser::isRewardDrawed($user_id,$current_day)){
                    return response()->json([
                        's'=>0,'m'=>'獎勵已領取。'
                    ]);
                }

                FreqEventUser::drawReward($user_id,$current_day,$event->id);
                
                break;
            case Event::TYPE_ONETIME;

                if($event->isRewardDrawed($user_id)){
                    return response()->json([
                        's'=>0,'m'=>'獎勵已領取。'
                    ]);
                }
                
                break;
            default:
                break;
        }

        $user = User::find($user_id);
        $rewardAmount = $event->rewardAmount();
        Log::channel('translog')->info('user '.$user_id.' get money '.$rewardAmount);
        try {
            //使用者加錢
            $user->updateWallet(true,$rewardAmount);

            //註記已領取
            $event->drawReward($user_id);

            //新增交際紀錄
            Transaction::create([
                'tran_id'=>time() . rand(10,99),
                'user_id'=>$user->id,
                'event' =>'活動獎勵-' . $event->title,
                'amount'=>$rewardAmount,
                'target_id'=>0,
                'give_take'=>true,
            ]);
        } catch (\Throwable $th) {
            return response($th);
        }

        Log::channel('eventlog')->info('user '.$user_id.' draw event '.$event->id.' reward success');
        return response()->json([
            's'=>1,'m'=>'您已成功領取活動參與獎勵。'
        ]);

    }


    



    //----------------district-----------------
    public function GetDistrict(){

        $districts = DB::table('districts')->get();

        return response()->json($districts);
    }

}
