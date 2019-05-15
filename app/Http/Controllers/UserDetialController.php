<?php

namespace App\Http\Controllers;

use App\UserDetial;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;

class UserDetialController extends Controller
{

    
    public function uploadImage(Request $request)
    {
        
        //handle image
        $image = $request->image;  // your base64 encoded
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = time().'.'.'png';
        $path = 'images/users/'.$request->name;

        if(!Storage::exists($path)){
            Storage::makeDirectory($path);   
        }else{
            \File::cleanDirectory($path);
        }

        \File::put($path.'/'.$imageName, base64_decode($image));
        
        $user = User::find($request->id);
        $user_detial = $user->detial;        
        $user_detial->img = $imageName;
        $user_detial->save();


        return response()->json([
            'status'=>'ok',
            'image_name'=>$imageName,
        ]); 
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UserDetial  $userDetial
     * @return \Illuminate\Http\Response
     */
    public function show(UserDetial $userDetial)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\UserDetial  $userDetial
     * @return \Illuminate\Http\Response
     */
    public function edit(UserDetial $userDetial)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserDetial  $userDetial
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserDetial $userDetial)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UserDetial  $userDetial
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserDetial $userDetial)
    {
        //
    }
}
