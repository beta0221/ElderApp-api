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
        $user_image = '';
        if($user->img){
            $user_image = config('app.static_host') . "/users/$user->id_code/$user->img";    
        }

        return [
            'user_name' => $user->name,
            'user_image' => $user_image,
            'title' => $this->title,
            'body' => $this->body,
            'likes' => $this->likes,
            'comments' => $this->comments,
            'created_at' => $this->created_at,
        ];
    }
}
