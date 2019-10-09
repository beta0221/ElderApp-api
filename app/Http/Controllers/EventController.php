<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
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
            
            $events = Event::where(function($query)use($request){

                if($request->category){
                    $query->where('category_id',$request->category);
                }

                if($request->district){
                    $query->where('district_id',$request->district);
                }


            })->orderBy('created_at','desc')->get();

            return $events;
        }
        
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
                'location'=>'required',
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


        try {
            if($request->hasFile('file')){
                $file = $request->file('file');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $request->merge(['image'=>$filename]);
                $path = "/images/events/" . $event_slug . "/";
                
                if(!Storage::disk('local')->exists($path)){
                    
                    if(!Storage::disk('local')->put($path . $filename,File::get($file))){
                        return response()->json([
                            's'=>0,
                            'm'=>'檔案無法儲存',
                        ]);    
                    }
                    
                }
                
            }
        } catch (\Throwable $th) {
            return response()->json($th);
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
            
        }else{
            return response()->json([
                's'=>0,
                'm'=>'Event not found!'
            ]);
        }
        
        
        
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

        $unixDeadline=strtotime($event->deadline);
        $unixNow=time();
        if($unixNow>$unixDeadline){
            return response()->json([
                's'=>0,
                'm'=>'報名已截止!'
            ]);
        }else{
            if($event){
                $user=User::where('id',$request->id)->first();
                if(!$user->go_events()->find($event->id)){
                    $user->go_events()->attach($event->id);
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
            else{
                return response()->json([
                    's'=>0,
                    'm'=>'Event not found!'
                ]);
            }
        }

        

    }
    public function CancelEvent(Request $request,$slug){

        $event=Event::where('slug',$slug)->first();
        if($event){
            $user=User::where('id',$request->id)->first();
            if($user->go_events()->find($event->id)){
                $user->go_events()->detach($event->id);
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


//----------------district-----------------
public function GetDistrict(){

    $districts = DB::table('districts')->get();

    return response()->json($districts);
}

}
