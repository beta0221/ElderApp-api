<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LocationQuantityCollection extends ResourceCollection
{
    protected $locationDict;

    public function configureDict($locationDict){
        $this->locationDict = $locationDict;
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
            $locationQuantityResource = new LocationQuantityResource($resource);
            return $locationQuantityResource->configureDict($this->locationDict)->toArray($request);
        });
    }
}
