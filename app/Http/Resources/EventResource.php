<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{


    protected $rewardDict;

    public function configureDict($rewardDict){
        $this->rewardDict = $rewardDict;
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
        $imgUrl = null;
        if($this->image){
            $imgUrl = config('app.static_host') . "/events/$this->slug/$this->image";
        }

        $reward = '';
        if(isset($this->rewardDict[$this->reward_level_id])){
            $reward = $this->rewardDict[$this->reward_level_id]->reward;
        }
        
        return [
            'id'=>$this->id,
            'slug'=>$this->slug,
            'title'=>$this->title,
            'reward'=>$reward,
            'body'=>$this->body,
            'dateTime'=>$this->dateTime,
            'dateTime2'=>$this->dateTime2,
            'deadline'=>$this->deadline,
            'type'=>$this->type,
            'maximum'=>$this->maximum,
            'people'=>$this->people,
            'location'=>$this->location,
            'imgUrl'=>$imgUrl,
        ];

    }
}
