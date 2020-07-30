<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EventCollection extends ResourceCollection
{

    protected $catDict;
    protected $rewardDict;
    protected $districtDict;

    public function configureDict($catDict,$rewardDict,$districtDict){
        $this->catDict = $catDict;
        $this->rewardDict = $rewardDict;
        $this->districtDict = $districtDict;
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

            $imgUrl = null;
            if($resource->image){
                $imgUrl = config('app.static_host') . "/events/$resource->slug/$resource->image";
            }
            $cat = '';
            $district = '';
            $reward = '';
            if(isset($this->catDict[$resource->category_id])){
                $cat = $this->catDict[$resource->category_id]->name;
            }
            if(isset($this->rewardDict[$resource->reward_level_id])){
                $reward = $this->rewardDict[$resource->reward_level_id]->reward;
            }
            if(isset($this->districtDict[$resource->district_id])){
                $district = $this->districtDict[$resource->district_id]->name;
            }

            return [
                'name'=>$resource->title,
                'slug'=>$resource->slug,
                'type'=>$resource->event_type,
                'reward'=>$reward,
                'cat'=>$cat,
                'district'=>$district,
                'location'=>$resource->location,
                'maximum'=>$resource->maximum,
                'people'=>$resource->people,
                'dateTime'=>$resource->dateTime,
                'imgUrl'=>$imgUrl,
            ];
        });
    }
}
