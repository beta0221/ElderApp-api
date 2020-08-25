<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comment';
    protected $guarded=[];    

    const MAX_COMMENTS_PER_DAY = 3;

    public function removeCommentFromPost(){
        $post = Post::findOrFail($this->post_id);
        $post->comments -= 1;
        $post->save();

        $this->delete();
    }

    /**留言是否已達當日上限 */
    public static function checkCommentMax($user_id){
        $count = Comment::where('user_id',$user_id)
        ->whereDate('created_at', \Carbon\Carbon::today())
        ->count();

        if($count >= static::MAX_COMMENTS_PER_DAY){
            return false;
        }
        return true;
    }
}
