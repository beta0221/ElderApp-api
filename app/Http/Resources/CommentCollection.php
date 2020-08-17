<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentCollection extends ResourceCollection
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

            return [
                'user_name'=>$user_name,
                'user_image'=>$user_image,
                'body'=>$resource->body,
                'created_at'=>$resource->created_at,
            ];
        });



    }
}
