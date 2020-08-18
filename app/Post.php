<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    protected $table = 'post';
    protected $guarded=[];    

    /**
     * 是否按過讚 
     * @return Boolean
     * */
    public function hasLikedBy($user_id){
        if(DB::table('post_like')->where('user_id',$user_id)->where('post_id',$this->id)->first()){
            return true;
        }
        return false;
    }

    /**按讚 */
    public function likeBy($user_id){
        if($this->hasLikedBy($user_id)){    //已經按過
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

    /**收回讚 */
    public function unLikeBy($user_id){
        if(!$this->hasLikedBy($user_id)){   //沒按過
            return false;
        }
        DB::table('post_like')->where('user_id',$user_id)->where('post_id',$this->id)->delete();

        $this->likes -= 1;
        $this->save();
        return true;
    }

    /**
     * 留言
     * @return Comment
     *  */
    public function makeComment($user_id,$comment){
        $this->comments += 1;
        $this->save();

        $comment = Comment::create([
            'post_id'=>$this->id,
            'user_id'=>$user_id,
            'body'=>$comment
        ]);

        return $comment;
    }

}
