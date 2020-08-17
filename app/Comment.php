<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comment';
    protected $guarded=[];    


    public function removeCommentFromPost(){
        $post = Post::findOrFail($this->post_id);
        $post->comments -= 1;
        $post->save();

        $this->delete();
    }
}
