<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Category;
use App\EventCertificate;
use App\FreqEventUser;
use App\Helpers\ControllerTrait;
use App\Helpers\ImageResizer;
use App\Helpers\Pagination;
use App\Helpers\Tracker;
use App\Http\Resources\EventCollection;
use App\Http\Resources\EventResource;
use App\Jobs\NotifyAppUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class EventController extends Controller
{

    use ControllerTrait;
    
    public function __construct()
    {
        $this->middleware(['JWT','BCP'], ['only' => ['index','store','destroy','update','getRewardLevel','getEventManagers','addManager','removeManager','show_certificate']]);
        $this->middleware(['JWT'],['only'=>['myEventList','eventDetail','drawEventRewardV2']]);
        $this->middleware(['webAuth'], ['only' => ['view_myCourse']]);
    }

    /**
     * 後台用Display a listing of the resource. 
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $p = new Pagination($request);

        $request_user = request()->user();
        $query = DB::table('events')->orderBy($p->orderBy, $p->ascOrdesc);

        if($request_user->hasRole('teacher')){
            $query->where('owner_id',$request_user->id);
        }

        if(!$request_user->hasRole('admin')){
            $query->where('association_id',$request_user->association_id);
        }

        if($request->has('public')){
            $query->where('public',$request->public);
        }

        $total = $query->count();
        $query->skip($p->skip)->take($p->rows);
        $owner_id_array = $query->pluck('owner_id');
        $events = $query->get();

        $nameDict = User::getNameDictByIdArray($owner_id_array);
        foreach ($events as $event) {
            $event->owner = null;
            if(isset($nameDict[$event->owner_id])){
                $event->owner = $nameDict[$event->owner_id];
            }
        }

        return response()->json([
            'events' => $events,
            'total' => $total,
            'staticHost' => config('app.static_host'),
        ]);

    }

    //手機
    public function getEvents(Request $request){
        $events = Event::where('public',1)->where(function($query)use($request){

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

    /**手機 活動列表夜 v2 */
    public function eventList(Request $request){

        $page = ($request->page)?$request->page:1;
        $rows = 10;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';

        $total = Event::where('public',1)->count();
        $eventList = Event::where('public',1)->skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get();

        $eventList = $this->convertEventColleection($eventList);

        $hasNextPage = true;
        if(($skip + $rows) >= $total){ $hasNextPage = false; }

        return response([
            'eventList'=>$eventList,
            'hasNextPage'=>$hasNextPage
        ]);

    }
 
    /**
     * 後台用Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Tracker::log($request);
        
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

        $user = request()->user();
        $request['association_id'] = $user->association_id;
        if($user->hasRole('teacher')){
            $request['owner_id'] = $user->id;
            $request['reward_level_id'] = 1;
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
        $request->merge(['body'=>$this->resizeHtmlImg($request->body)]);
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

    /**
     * 存入圖檔(目前在導入file sever 階段 必須同時執行 本地 以及 file sever 處理速度會很慢，等舊版本App更新才能完全轉移)
     */
    private function imageHandler($file,$event_slug,$type = 'events'){
        
        $filename = time() . '.' . $file->getClientOriginalExtension();
        //$path = "/images/events/" . $event_slug . "/";
        $ftpPath = "/$type/$event_slug/";

        // if(Storage::disk('local')->exists($path)){
        //     $result = Storage::deleteDirectory($path);
        //     if(!$result){
        //         return false;
        //     }
        // }
        if(Storage::disk('ftp')->exists($ftpPath)){
            $result = Storage::disk('ftp')->deleteDirectory($ftpPath);
            if(!$result){
                return false;
            }
        }
        
        $img = ImageResizer::aspectFit($file,400)->encode();
        // if(!Storage::disk('local')->put($path . $filename,$img)){
        //     return false;//失敗:回傳false
        // }
        if(!Storage::disk('ftp')->put($ftpPath . $filename,$img)){
            return false;
        }

        if($type == 'events'){ return $filename; }  //成功：回傳檔名
        return config('app.static_host'). $ftpPath . $filename;
        
    }


    /**
     * 後台用 Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $event= Event::where('slug',$slug)->first();

        // $imgUrl = config('app.static_host') . "/events/$event->slug/$event->image";
        // $event['imgUrl'] = $imgUrl;
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
     * App Users 產品內頁 api v2
     */
    public function eventDetail($slug){
        
        $event = Event::where('slug',$slug)->firstOrFail();
        $rewardDict = Event::getRewardDict();

        $user = auth()->user();
        $isParticipated = $event->isParticipated($user->id);

        $event = new EventResource($event);
        $event = $event->configureDict($rewardDict);

        return response([
            'event'=>$event,
            'isParticipated'=>$isParticipated
        ]);

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
        Tracker::log($request);
        
        if(!$event=Event::where('slug',$slug)->first()){
            return response()->json([
                's'=>0,'m'=>'Event not found!'
            ]);
        }
        Tracker::info(json_encode($event));
        
        $user = request()->user();
        if($user->hasRole('teacher')){
            if($event->owner_id != $user->id){
                return response()->json([
                    's'=>0,'m'=>'Event not found!'
                ]);
            }
            unset($request['reward_level_id']);
        }

        switch ($request->event_type) {
            case Event::TYPE_FREQUENTLY:
                unset($request['days']);
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

        $request->merge(['body'=>$this->resizeHtmlImg($request->body)]);
        try {
            $event->update($request->except(['file']));
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
    public function destroy(Request $request,$slug)
    {
        Tracker::log($request);
        
        $event=Event::where('slug',$slug)->first();
        if($event){
            // $event->delete();
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

    public function getRewardLevel(){
        // $user = Auth::user();
        // $reward_level = null;
        // if($user->hasAnyRole(['admin','employee'])){
        //     $reward_level = DB::table('reward_level')->get();
        // }else{
        //     $reward_level = DB::table('reward_level')->where('id',1)->get();
        // }
        $reward_level = DB::table('reward_level')->get();
        return response($reward_level);
    }

    /**
     * App users 舊api
     */
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

    /**
     * App User 我參加的活動 v2
     */
    public function myEventList(Request $request){
        
        $page = ($request->page)?$request->page:1;
        $rows = 10;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';

        $user = auth()->user();
        $eventIdArray = $user->getUserEventsIdArray();
        
        $total = Event::whereIn('id',$eventIdArray)->where('public',1)->count();
        $eventList = Event::whereIn('id',$eventIdArray)->where('public',1)->skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get();
        
        $eventList = $this->convertEventColleection($eventList);

        $hasNextPage = true;
        if(($skip + $rows) >= $total){ $hasNextPage = false; }

        return response([
            'eventList'=>$eventList,
            'hasNextPage'=>$hasNextPage
        ]);
    }

    /**
     * eventList 轉型 EventCollection
     * @param array $eventList
     * @return array EventCollection
     */
    private function convertEventColleection($eventList){
        $districtIdArray = [];
        foreach ($eventList as $event) {
            $districtIdArray[] = $event->district_id;
        }

        $catDict = Category::getCatDict();
        $rewardDict = Event::getRewardDict();
        $districtDict = Event::getDistrictDict($districtIdArray);
        
        $eventList = new EventCollection($eventList);
        $eventList = $eventList->configureDict($catDict,$rewardDict,$districtDict);

        return $eventList;
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

                if(FreqEventUser::isRewardDrawed($user_id,$event->id,$current_day)){
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
        $event->drawReward($user->id);//註記已領取
        $user->update_wallet_with_trans(User::INCREASE_WALLET,$rewardAmount,"活動獎勵-".$event->title);//使用者加錢
        $user->increaseRank(1);//經驗值
        NotifyAppUser::dispatch($user->id,'恭喜您！','由於您積極參與活動於是榮譽點數提升了。');

        Log::channel('translog')->info('user '.$user->id.' get money '.$rewardAmount);
        Log::channel('eventlog')->info('user '.$user->id.' draw event '.$event->id.' reward success');

        return response()->json([
            's'=>1,'m'=>'您已成功領取活動參與獎勵。'
        ]);

    }


    public function drawEventRewardV2(Request $request,$slug){
        
        $user = auth()->user();

        if(!$event = Event::where('slug',$slug)->first()){
            return response()->json([
                's'=>0,'m'=>'Event not found!'
            ]);
        }

        if(!$event->isParticipated($user->id)){
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
                if(FreqEventUser::isRewardDrawed($user->id,$event->id,$current_day)){
                    return response()->json([
                        's'=>0,'m'=>'獎勵已領取。'
                    ]);
                }
                FreqEventUser::drawReward($user->id,$current_day,$event->id);
                break;
            case Event::TYPE_ONETIME;
                if($event->isRewardDrawed($user->id)){
                    return response()->json([
                        's'=>0,'m'=>'獎勵已領取。'
                    ]);
                }
                break;
            default:
                break;
        }

        $rewardAmount = $event->rewardAmount();
        $event->drawReward($user->id);//註記已領取
        $user->update_wallet_with_trans(User::INCREASE_WALLET,$rewardAmount,"活動獎勵-".$event->title);//使用者加錢
        $user->increaseRank(1);//經驗值
        NotifyAppUser::dispatch($user->id,'恭喜您！','由於您積極參與活動於是榮譽點數提升了。');

        Log::channel('translog')->info('user '.$user->id.' get money '.$rewardAmount);
        Log::channel('eventlog')->info('user '.$user->id.' draw event '.$event->id.' reward success');
        
        return response()->json([
            's'=>1,'m'=>'您已成功領取活動參與獎勵。'
        ]);
    }


    public function updateEventPublicStatus(Request $request){
        // $request->event_id
        // $request->public
        if(!$event = Event::find($request->event_id)){
            return response()->json(['s'=>0,'m'=>'event not found']);
        }

        // $user = Auth::user();
        // if($user->hasRole('teacher')){
        //     if($event->owner_id != $user->id){
        //         return response()->json(['s'=>0,'m'=>'權限不足']);
        //     }
        // }

        $event->public = $request->public;
        $event->save();

        return response()->json([
            's'=>1,'public'=>$request->public
        ]);
    }


    public function universal_link(Request $request,$slug){
        $event = Event::where('slug',$slug)->firstOrFail();
        $rewardDict = Event::getRewardDict();

        $event = new EventResource($event);
        $event = $event->configureDict($rewardDict);

        return view('event.detail_link',[
            'event'=>(object)$event->toArray($request)
        ]);

    }


    //----------------district-----------------
    public function GetDistrict(){

        $districts = DB::table('districts')->get();

        return response()->json($districts);
    }

    public function view_myCourse(Request $request){
        if($request->token){
            Cookie::queue('token',$request->token,60);
        }
        $user = $request->user();

        $eventList = Event::where('owner_id',$user->id)->orderBy('id','desc')->get();
        $dict = [];
        foreach ($eventList as $event) { $dict[$event->id] = true; }

        //manage events
        $manage_eventList = $user->manage_events()->orderBy('id','desc')->get();
        foreach ($manage_eventList as $event) {
            if(!isset($dict[$event->id])){
                $eventList[] = $event;
            }
        }

        return view('event.eventList',[
            'user'=>$user,
            'eventList'=>$eventList,
        ]);
        
    }

    //----------------managers-----------------

    public function getEventManagers($slug){
        $event = Event::where('slug',$slug)->firstOrFail();
        $managers = $event->managers()->get();
        return response($managers);
    }

    public function addManager(Request $request,$slug){
        Tracker::log($request);

        $user = User::findOrFail($request->user_id);
        $event = Event::where('slug',$slug)->firstOrFail();
        if(!$manager = $event->managers()->find($user->id)){
            $event->managers()->attach($user->id);
        }
        return response('success');
    }

    public function removeManager(Request $request,$slug){
        Tracker::log($request);

        $user = User::findOrFail($request->user_id);
        $event = Event::where('slug',$slug)->firstOrFail();
        $event->managers()->detach($user->id);
        return response('success');
    }



    //--------------------Certificates-------------------

    public function show_certificate($slug){
        $event = Event::where('slug',$slug)->firstOrFail();
        if($event->certificate){
            return response($event->certificate);
        }
        return response('no certificate');
    }


    public function store_certificate(Request $request,$slug){
        $event = Event::where('slug',$slug)->firstOrFail();
        if($event->certificate){
            return response('已存在活動認證',400);
        }

        if($request->hasFile('image')){
            if(!$filename = $this->imageHandler($request->file('image'),$slug,'eventCertificates')){
                return response('檔案無法儲存',500);
            }
        }

        $certificate = new EventCertificate([
            'reward'=>$request->reward,
            'image'=>$filename,
        ]);

        $event->certificate()->save($certificate);
        return response('success');
    }


    public function update_certificate(Request $request,$slug){
        $event = Event::where('slug',$slug)->firstOrFail();
        if(!$event->certificate){ return response('無活動認證',404); }

        $certificate = [
            'reward' => $request->reward,
        ];

        if($request->hasFile('image')){
            if(!$filename = $this->imageHandler($request->file('image'),$slug,'eventCertificates')){
                return response('檔案無法儲存',500);
            }
            $certificate['image'] = $filename;
        }

        $event->certificate()->update($certificate);
        return response('success');
    }

    /**發送畢業認證 */
    public function issue_certificate($slug){
        $event = Event::where('slug',$slug)->firstOrFail();
        if(!$event->isFinished()){
            return response('課程尚未結束，無法頒發結業證書。',400);
        }

        if($event->hasIssued()){
            return response('課程已頒發過結業證書。',400);
        }

        $event->issueCertificateToUsers();
        $event->public = 0;
        $event->save();
        
        return response('success');
    }

}
