<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    protected $table = 'post';
    protected $guarded=[];    


    public function likeBy($user_id){
        if(DB::table('post_like')->where('user_id',$user_id)->where('post_id',$this->id)->first()){
            return false;
        }

        DB::table('post_like')->insert([
            'user_id'=>$user_id,
            'post_id'=>$this->id,
            'created_at'=>\Carbon\Carbon::now(),
            'updated_at'=>\Carbon\Carbon::now(),
        ]);
        
        $this->likes += 1;
        $this->save();
        return true;
        
    }

    public function unLikeBy($user_id){
        if(!$row = DB::table('post_like')->where('user_id',$user_id)->where('post_id',$this->id)->first()){
            return false;
        }
        DB::table('post_like')->where('user_id',$user_id)->where('post_id',$this->id)->delete();

        $this->likes -= 1;
        $this->save();
        return true;
    }

    public function makeComment($user_id,$comment){
        $this->comments += 1;
        $this->save();

        Comment::create([
            'post_id'=>$this->id,
            'user_id'=>$user_id,
            'body'=>$comment
        ]);
    }

}
