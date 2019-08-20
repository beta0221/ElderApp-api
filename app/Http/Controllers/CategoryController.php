<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Category::orderBy('created_at','desc')->get();
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
                'name'=>'required'
                
            ]);
        }catch(Exception $e){
            return response($e);
        }

        $request['slug']='A'.time();

        //--------------------------------------------------
        $category=Category::create($request->all());
        //--------------------------------------------------
        
        return response()->json([
            's'=>1,
            'event'=>$category,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $category= Category::where('slug',$slug)->first();
        //-----------------------------------------------------------------是否有登入
       if($category){
        return response()->json([
            's'=>1,
            'category'=>$category,
        ]);
       }else{
            return response()->json([
                's'=>0,
                'm'=>'Category not found!'
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        $category=Category::where('slug',$slug)->first();
        if($category){
            
            if($request['name']){
                $category->update(['name'=>$request->name]);
            }
            
            return response()->json([
                's'=>1,
                'category'=>$category,
                'm'=>'update success!'
            ]);
            
        }else{
            return response()->json([
                's'=>0,
                'm'=>'Category not found!'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $category=Category::where('slug',$slug)->first();
        if($category){
            $category->delete();
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
}
