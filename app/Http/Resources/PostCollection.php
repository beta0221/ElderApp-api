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
            if(isset($this->userDict[$resource->user_id])){
                $user = $this->userDict[$resource->user_id];
                $user_name = $user->name;
                if($user->img){
                    $user_image = config('app.static_host') . "/users/$user->id_code/$user->img";    
                }
            }

            if(strlen($resource->body) > 15){
                $resource->body = substr($resource->body,0,15) . '...';
            }

            return [
                'user_name'=>$user_name,
                'user_image'=>$user_image,
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
