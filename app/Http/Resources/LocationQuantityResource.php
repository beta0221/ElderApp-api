<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LocationQuantityResource extends JsonResource
{

    protected $locationDict;

    public function configureDict($locationDict){
        $this->locationDict = $locationDict;
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

        $name = '';
        $slug = '';
        $address = '';
        $link = '';

        if(isset($this->locationDict[$this->location_id])){
            $location = $this->locationDict[$this->location_id];
            $name = $location->name;
            $slug = $location->slug;
            $address = $location->address;
            $link = $location->link;
        }

        return [
            'name'=>$name,
            'slug'=>$slug,
            'address'=>$address,
            'link'=>$link,

            'location_id'=>$this->location_id,
            'quantity'=>$this->quantity,
            'quantity_cash'=>$this->quantity_cash,
        ];
    }

    public static function collection($resource){
        return new LocationQuantityCollection($resource);
    }

}
