<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{


    protected $userDict;
    
    public function configureDict($userDict){
        $this->userDict = $userDict;
        return $this;
    }


    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function($resource) use($request){

            
            $user_name = '';
            $user_image = null;
            $post_image = null;
            if(isset($this->userDict[$resource->user_id])){
                $user = $this->userDict[$resource->user_id];
                $user_name = $user->name;
                if($user->img){
                    $user_image = config('app.static_host') . "/users/$user->id_code/$user->img";    
                }
            }
            if($resource->images){
                $post_image = config('app.static_host') . "/posts/$resource->slug/$resource->images";    
            }

            if(strlen($resource->body) > 20){
                $resource->body = mb_substr($resource->body,0,20,"utf-8") . '...';
            }

            return [
                'user_name'=>$user_name,
                'user_image'=>$user_image,
                'post_image'=>$post_image,
                'slug'=>$resource->slug,
                'title'=>$resource->title,
                'body'=>$resource->body,
                'likes'=>$resource->likes,
                'comments'=>$resource->comments,
                'created_at'=>$resource->created_at,
            ];
        });
    }
}
