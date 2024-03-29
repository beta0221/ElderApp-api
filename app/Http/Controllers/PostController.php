<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Helpers\ImageResizer;
use App\Helpers\Pagination;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Post;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    public function __construct()
    {
        $this->middleware('JWT',['only'=>[
            'myPostList',
        ]]);
        $this->middleware(['JWT','valid'], ['only' => [
            'makeNewPost',
            'likePost',
            'unLikePost',
            'commentOnPost',
            'removeComment',
        ]]);
    }

    /**文章內頁 */
    public function detail($slug){
        $post = Post::where('slug',$slug)->firstOrFail();
        $post = new PostResource($post);

        $isAuthor = false;
        $hasLiked = false;
        if($user = request()->user()){
            if($user->id == $post->user_id){ $isAuthor == true; }
            $hasLiked = $post->hasLikedBy($user->id);
        }
        
        return response()->json([
            'post'=>$post,
            'isAuthor'=>$isAuthor,
            'hasLiked'=>$hasLiked,
        ]);

    }

    /**文章列表 */
    public function list(Request $request){
        $p = new Pagination($request);

        $total = Post::count();
        $postList = Post::skip($p->skip)->take($p->rows)->orderBy($p->orderBy,$p->ascOrdesc)->get();

        $user_id_array = [];
        foreach ($postList as $post) {
            if(!in_array($post->user_id,$user_id_array)){
                $user_id_array[] = $post->user_id;
            }
        }

        $users = User::whereIn('id',$user_id_array)->get();
        $userDict = [];
        foreach ($users as $user) { $userDict[$user->id] = $user; }

        $postList = new PostCollection($postList);
        $postList = $postList->configureDict($userDict);
        
        return response()->json([
            'pagination'=>$p,
            'total'=>$total,
            'postList'=>$postList
        ]);
    }

    /** 我的所有po文 */
    public function myPostList(Request $request){
        $p = new Pagination($request);
        $user = request()->user();

        $total = Post::where('user_id',$user->id)->count();
        $postList = Post::where('user_id',$user->id)->skip($p->skip)->take($p->rows)->orderBy($p->orderBy,$p->ascOrdesc)->get();

        $userDict = [];
        $userDict[$user->id] = $user;

        $postList = new PostCollection($postList);
        $postList = $postList->configureDict($userDict);

        return response()->json([
            'pagination'=>$p,
            'total'=>$total,
            'postList'=>$postList
        ]);
    }

    /**
     * 處理上傳的圖片
     * @return mixed false 失敗 imageName(String)成功
     */
    private function handleImage($image,$slug){
        
        $imageName = $slug.'.'.'png';
        $ftpPath = "/posts/$slug/";
        if(Storage::disk('ftp')->exists($ftpPath)){
            if(!$result = Storage::disk('ftp')->deleteDirectory($ftpPath)){
                return false;
            }
        }
        
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        //$image = base64_decode($image);
        $image = ImageResizer::aspectFit($image,400)->encode();

        if(!Storage::disk('ftp')->put($ftpPath . $imageName,$image)){
            return false;
        }

        return $imageName;
    }

    /** po文 */
    public function makeNewPost(Request $request){
        $this->validate($request,[
            'title'=>'required',
            'body'=>'required',
        ]);
        $user = request()->user();

        if(!Post::checkPostMax($user->id)){
            return response(['alert'=>'已達當日發文上限','m'=>'error']);
        }

        $slug = 'P' . uniqid();
        if(!empty($request->image)){
            $imageName = $this->handleImage($request->image,$slug);
            if(empty($imageName)){
                return response(['m'=>'error'],500);
            }
            $request['images'] = $imageName;
        }

        $request->request->remove('image');
        $request->merge([
            'slug'=>$slug,
            'user_id'=>$user->id
        ]);

        try {
            Post::create($request->all());
        } catch (\Throwable $th) {
            return response(['m'=>$th],400);
        }
        
        return response(['m'=>'success'],200);

    }

    /**按讚 */
    public function likePost($slug){
        $post = Post::where('slug',$slug)->firstOrFail();
        $user = request()->user();
        
        if(!Post::checkLikeMax($user->id)){
            return response(['alert'=>'已達當日按讚上限','m'=>'error']);
        }

        try {
            if(!$result = $post->likeBy($user->id)){
                return response(['m'=>'您已按讚'],200);
            }
        } catch (\Throwable $th) {
            return response(['m'=>$th],400);
        }
        
        return response(['m'=>'success'],200);
    }

    /**收回讚 */
    public function unLikePost($slug){
        $post = Post::where('slug',$slug)->firstOrFail();
        if($post->likes == 0){ return response('success',200); }
        $user = request()->user();

        try {
            if(!$result = $post->unLikeBy($user->id)){
                return response(['m'=>'您已收回讚'],200);
            }
        } catch (\Throwable $th) {
            return response(['m'=>$th],400);
        }
        
        return response(['m'=>'success'],200);
    }

    /**在po文下面留言 */
    public function commentOnPost(Request $request,$slug){
        $this->validate($request,[
            'comment'=>'required'
        ]);

        $post = Post::where('slug',$slug)->firstOrFail();
        $user = request()->user();

        if(!Comment::checkCommentMax($user->id)){
            return response(['alert'=>'已達當日留言上限','m'=>'error']);
        }

        try {
            $comment = $post->makeComment($user->id,$request->comment);
        } catch (\Throwable $th) {
            return response(['m'=>$th],400);
        }

        $userDict = [];
        $userDict[$user->id] = $user;

        $comment = new CommentResource($comment);
        $comment = $comment->configureDict($userDict);

        return response([
            'comment'=>$comment,
        ],200);
    }

    /**刪除留言 */
    public function removeComment(Request $request){
        $this->validate($request,[
            'comment_id'=>'required'
        ]);
        $user = request()->user();

        $comment = Comment::findOrFail($request->comment_id);
        if($comment->user_id != $user->id){
            return response('error',400);
        }

        try {
            $comment->removeCommentFromPost();
        } catch (\Throwable $th) {
            return response($th,400);
        }

        return response('success',200);

    }

    /**文章中的留言 */
    public function commentList(Request $request,$slug){
        $p = new Pagination($request);

        $post = Post::where('slug',$slug)->firstOrFail();
        $total = Comment::where('post_id',$post->id)->count();
        $commentList = Comment::where('post_id',$post->id)->skip($p->skip)->take($p->rows)->orderBy($p->orderBy,$p->ascOrdesc)->get();

        $user_id_array = [];
        foreach ($commentList as $comment) {
            if(!in_array($comment->user_id,$user_id_array)){
                $user_id_array[] = $comment->user_id;
            }
        }

        $users = User::whereIn('id',$user_id_array)->get();
        $userDict = [];
        foreach ($users as $user) { $userDict[$user->id] = $user; }

        $commentList = new CommentCollection($commentList);
        $commentList = $commentList->configureDict($userDict);

        $hasNextPage = true;
        if(($p->skip + $p->rows) >= $total){ $hasNextPage = false; }

        return response([
            'hasNextPage'=>$hasNextPage,
            'commentList'=>$commentList,
        ]);
    }

    public function universal_link(Request $request,$slug){
        $post = Post::where('slug',$slug)->firstOrFail();
        $post = new PostResource($post);

        $commentList = Comment::where('post_id',$post->id)->orderBy('id','desc')->take(10)->get();
        $user_id_array = [];
        foreach ($commentList as $comment) {
            if(!in_array($comment->user_id,$user_id_array)){
                $user_id_array[] = $comment->user_id;
            }
        }

        $users = User::whereIn('id',$user_id_array)->get();
        $userDict = [];
        foreach ($users as $user) { $userDict[$user->id] = $user; }

        $commentList = new CommentCollection($commentList);
        $commentList = $commentList->configureDict($userDict);
        
        return view('post.detail_link',[
            'post'=>(object)$post->toArray($request),
            'commentList'=>$commentList->toArray($request),
        ]);        
    }

}
