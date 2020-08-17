<?php

namespace App\Http\Controllers;

use App\Helpers\Pagination;
use App\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{

    public function __construct()
    {
        $this->middleware('JWT', ['only' => 
            [
                'makeNewPost',
                'likePost',
                'unLikePost',
            ]
        ]);
    }


    public function list(Request $request){
        $p = new Pagination($request);

        $total = Post::count();
        $postList = Post::skip($p->skip)->take($p->rows)->orderBy($p->orderBy,$p->ascOrdesc)->get();

        return response([
            'total'=>$total,
            'postList'=>$postList
        ]);
    }

    public function makeNewPost(Request $request){
        
        $user = request()->user();
        $request->merge([
            'slug'=>'P' . uniqid(),
            'user_id'=>$user->id
        ]);

        try {
            Post::create($request->all());
        } catch (\Throwable $th) {
            return response($th,400);
        }
        
        return response('success',200);

    }

    public function likePost($slug){
        $post = Post::where('slug',$slug)->firstOrFail();
        $user = request()->user();
        
        try {
            if(!$result = $post->likeBy($user->id)){
                return response('您已按讚',200);
            }
        } catch (\Throwable $th) {
            return response($th,400);
        }
        
        return response('success',200);
    }

    public function unLikePost($slug){
        $post = Post::where('slug',$slug)->firstOrFail();
        if($post->likes == 0){ return response('success',200); }
        $user = request()->user();

        try {
            if(!$result = $post->unLikeBy($user->id)){
                return response('您已收回讚',200);
            }
        } catch (\Throwable $th) {
            return response($th,400);
        }
        
        return response('success',200);
    }


}
