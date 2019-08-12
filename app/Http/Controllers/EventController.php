<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use App\User;

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
    public function index()
    {
        // return Event::all();
        return Event::orderBy('created_at','desc')->get();
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
            ]);
        }catch(Exception $e){
            return response($e);
        }
        // $request['slug']=$request->title.time();
        $request['slug']='A'.time();
        // $request['slug']='123';
        //--------------------------------------------------
        $event=Event::create($request->all());
        //--------------------------------------------------
        
        return response()->json([
            's'=>1,
            'event'=>$event,
        ]);
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
            
            if($request['title']){
                $event->update(['title'=>$request->title]);
            }
            if($request['body']){
                $event->update(['body'=>$request->body]);
            }
            if($request['dateTime']){
                $event->update(['dateTime'=>$request->dateTime]);
            }
            if($request['location']){
                $event->update(['location'=>$request->location]);
            }
            if($request['deadline']){
                $event->update(['deadline'=>$request->deadline]);
            }
            // $event=Event::where('slug',$slug)->first();
            return response()->json([
                's'=>1,
                'event'=>$event,
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
        if($event){
            return $event->guests()->get();
        }else{
            return response()->json([
                's'=>0,
                'm'=>'Event not found!'
            ]);
        }

    }

    public function JoinEvent(Request $request,$slug){

        $event=Event::where('slug',$slug)->first();
        if($event){
            $user=User::where('id',$request->id)->first();
            if(!$user->go_events()->find($event->id)){
                $user->go_events()->attach($event->id);
                return response()->json([
                    's'=>1,
                    'm'=>'Joint event success!'
                ]);
            }else{
                return response()->json([
                    's'=>0,
                    'm'=>'Already join'
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
}
