<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Transaction;
use App\User;
use App\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class EventController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('JWT', ['except' => 'index']);
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        if($request->page){
            //管理後台
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
            ->skip($skip)
            ->take($rows)
            ->get();

            $total = DB::table('events')->count();

            return response()->json([
                'events' => $events,
                'total' => $total,
            ]);

        }else{
            //手機
            $this->getEvents($request);
        }
        
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
                'dateTime'=>'required',
                'dateTime_2'=>'required',
                'location'=>'required',
                'maximum'=>'required|min:1|integer',
                'deadline'=>'required',
                'category'=>'required',
                'district_id'=>'required',
                'file'=>'sometimes|nullable|image',
            ]);
        }catch(Exception $e){
            return response($e);
        }

        $event_slug='A'.time();
        $request['slug']=$event_slug;
        $name=$request['category'];
        unset($request['category']);

        $unixNow=time();
        $unixDeadline=strtotime($request['deadline']);
        $unixDateTime=strtotime($request['dateTime']);
        if($unixNow>$unixDeadline || $unixNow>$unixDateTime || $unixDeadline>$unixDateTime)
        {
            return response()->json([
                's'=>0,
                'm'=>'活動時間與截止期限設定錯誤!'
            ]);
        }


        
        if($request->hasFile('file')){
            $filename = $this->imageHandler($request->file('file'),$event_slug);
            
            if($filename){
                $request->merge(['image'=>$filename]);
            }else{
                return response()->json([
                    's'=>0,
                    'm'=>'檔案無法儲存',
                ]);
            }   
        }

        $category=Category::where('name',$name)->first();
        if($category){
            $request['category_id']=$category->id;
            //--------------------------------------------------
            $event=Event::create($request->except('file'));
            //--------------------------------------------------
            
            return response()->json([
                's'=>1,
                'event'=>$event,
            ]);
        }else{
            return response()->json([
                's'=>0,
                'm'=>'Category not found!'
            ]);
        }
        
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
        
        $event=Event::where('slug',$slug)->first();
        
        if($event){
            
            $unixNow=time();
            $unixDeadline=strtotime($request['deadline']);
            $unixDateTime=strtotime($request->dateTime);

            if($unixNow>$unixDeadline || $unixNow>$unixDateTime || $unixDeadline>$unixDateTime)
            {
                return response()->json([
                    's'=>0,
                    'm'=>'活動時間與截止期限設定錯誤!'
                ]);
            }
            
            $category=Category::where('name',$request->category)->first();
            if(!$category){
                return response()->json([
                    's'=>0,
                    'm'=>'Category not found!'
                ]);
            }else{
                $request->merge(['category_id'=>$category->id]);
                unset($request['category']);
            }

            if($request->hasFile('file')){
                $filename = $this->imageHandler($request->file('file'),$event->slug );
                if($filename){
                    $request->merge(['image'=>$filename]);
                }else{
                    return response()->json([
                        's'=>0,
                        'm'=>'檔案無法儲存',
                    ]);
                }   
            }

            if($event->maximum > $request->maximum){
                unset($request['maximum']);
            }


            try {
                $event->update($request->except('file'));
            } catch (\Throwable $th) {
                return response($th);
            }            

            return response()->json([
                's'=>1,
                // 'event'=>$event,
                'm'=>'update success!'
            ]);
            
        }

        return response()->json([
            's'=>0,
            'm'=>'Event not found!'
        ]);
        
        
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

        $event=Event::where('slug',$slug)->first();

        if(!$event){
            return response()->json([
                's'=>0,
                'm'=>'Event not found!'
            ]);
        }

        $unixDeadline=strtotime($event->deadline);
        $unixNow=time();
        if($unixNow>$unixDeadline){
            return response()->json([
                's'=>0,
                'm'=>'報名已截止!'
            ]);
        }

        if($event->maximum <= $event->numberOfPeople()){
            return response()->json([
                's'=>0,
                'm'=>'此活動已達人數上限。'
            ]);
        }

        $user=User::where('id',$request->id)->first();
        if(!$user->go_events()->find($event->id)){
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
                $user->go_events()->detach($event->id);
                $event->peopleDecrease();
                return response()->json([
                    's'=>1,
                    'm'=>'Cancel event success!'
                ]);
            }else{
                return response()->json([
                    's'=>0,
                    'm'=>'Already cancel'
                ]);
            }
            
        }
        else{
            return response()->json([
                's'=>0,
                'm'=>'Event not found!'
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
        $event = Event::where('slug',$slug)->first();
        if($event){
            return view('event.qrcode',[
                'event'=>$event,
            ]);
        }else{
            return response('活動不存在');
        }
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

        if($event->isRewardDrawed($user_id)){
            return response()->json([
                's'=>0,
                'm'=>'獎勵已領取。'
            ]);
        }

        try {
            //使用者加錢
            $user = User::find($user_id);
            $rewardAmount = $event->rewardAmount();
            $user->updateWallet(true,$rewardAmount);

            //註記已領取
            $event->drawReward($user_id);

            //新增交際紀錄
            Transaction::create([
                'tran_id'=>time() . rand(10,99),
                'user_id'=>$user->id,
                'event' =>'活動獎勵' . $event->title,
                'amount'=>$rewardAmount,
                'target_id'=>0,
                'give_take'=>true,
            ]);
        } catch (\Throwable $th) {
            return response($th);
        }

        return response()->json([
            's'=>1,
            'm'=>'您已成功領取活動參與獎勵。'
        ]);

    }




    //----------------district-----------------
    public function GetDistrict(){

        $districts = DB::table('districts')->get();

        return response()->json($districts);
    }

}
