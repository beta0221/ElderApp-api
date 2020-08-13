<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['JWT','admin']);
    }

    public function upload(Request $request,$type,$slug){
        $this->validate($request,[
            'upload'=>'required',
        ]);
        
        $originName = $request->file('upload')->getClientOriginalName();
        $fileName = pathinfo($originName, PATHINFO_FILENAME);
        $extension = $request->file('upload')->getClientOriginalExtension();
        $fileName = $fileName.'_'.time().'.'.$extension;

        $path = "";
        switch ($type) {
            case 'eventContent':
                $path = "/eventContent";
                break;
            case 'productContent':
                $path = "/productContent";
                break;
            default:
                return response('error',400);
                break;
        }

        if(!Storage::disk('ftp')->exists($path)){
            return response(['error'=>'path not found'],500);
        }
        
        $filePath = "$path/$slug/$fileName";
        $file = File::get($request->file('upload'));
        if(!Storage::disk('ftp')->put($filePath,$file)){
            return response(['error'=>'upload fail'],500);
        }

        $imgUrl = config('app.static_host') . $filePath;

        return response([
            'url'=>$imgUrl,
        ],200);
    }


}
