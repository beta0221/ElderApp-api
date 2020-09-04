<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $user = User::find($this->user_id);
        $user_image = null;
        $post_image = null;
        if($user->img){
            $user_image = config('app.static_host') . "/users/$user->id_code/$user->img";    
        }
        if($this->images){
            $post_image = config('app.static_host') . "/posts/$this->slug/$this->images";    
        }
        return [
            'user_name' => $user->name,
            'user_image' => $user_image,
            'post_image' => $post_image,
            'title' => $this->title,
            'body' => $this->body,
            'likes' => $this->likes,
            'comments' => $this->comments,
            'created_at' => $this->created_at,
        ];
    }
}
