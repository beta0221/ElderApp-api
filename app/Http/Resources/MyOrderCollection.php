<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MyOrderCollection extends ResourceCollection
{

    protected $productDict;
    protected $locationDict;

    public function configureDict($productDict,$locationDict){
        $this->productDict = $productDict;
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
            $myOrderResource = new MyOrderResource($resource);
            return $myOrderResource->configureDict($this->productDict,$this->locationDict)->toArray($request);
        });
    }
}
