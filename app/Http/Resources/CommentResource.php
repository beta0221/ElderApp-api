<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{

    protected $userDict;
    
    public function configureDict($userDict){
        $this->userDict = $userDict;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user_name = '';
        $user_image = null;
        if(isset($this->userDict[$this->user_id])){
            $user = $this->userDict[$this->user_id];
            $user_name = $user->name;
            if($user->img){
                $user_image = config('app.static_host') . "/users/$user->id_code/$user->img";    
            }
        }

        return [
            'user_name'=>$user_name,
            'user_image'=>$user_image,
            'body'=>$this->body,
            'created_at'=>$this->created_at,
        ];
    }
}
