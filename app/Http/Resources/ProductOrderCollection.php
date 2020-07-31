<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductOrderCollection extends ResourceCollection
{
    protected $locationDict;
    protected $userNameDict;

    public function configureDict($locationDict,$userNameDict){
        $this->locationDict = $locationDict;
        $this->userNameDict = $userNameDict;
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

            
            $location = '';
            $name = '';
            if(isset($this->locationDict[$resource->location_id])){
                $location = $this->locationDict[$resource->location_id]->name;
            }
            if(isset($this->userNameDict[$resource->user_id])){
                $name = $this->userNameDict[$resource->user_id];
            }

            return [
                'id'=>$resource->id,
                'name'=>$name,
                'location'=>$location,
                'created_at'=>substr($resource->created_at,0,10),
                'receive'=>$resource->receive,
            ];

        });
    }
}
