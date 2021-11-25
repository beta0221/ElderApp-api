<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MyOrderResource extends JsonResource
{

    protected $productDict;
    protected $locationDict;

    public function configureDict($productDict,$locationDict){
        $this->productDict = $productDict;
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
        $imgUrl = '';
        $location_name = '';
        $address = '';
        if(isset($this->productDict[$this->product_id])){
            $product = $this->productDict[$this->product_id];
            $name = $product->name;
            $imgUrl = config('app.static_host') . "/products/$product->slug/$product->img";
        }
        if(isset($this->locationDict[$this->location_id])){
            $location = $this->locationDict[$this->location_id];
            $location_name = $location->name;
            $address = $location->address;
        }

        return [
            'id'=>$this->id,
            'user_id'=>$this->user_id,
            'product_id'=>$this->product_id,
            'location_id'=>$this->location_id,
            'name'=>$name,
            'imgUrl'=>$imgUrl,
            'location_name'=>$location_name,
            'address'=>$address,
            'created_at'=>$this->created_at,
            'receive'=>$this->receive
        ];
    }

    public static function collection($resource){
        return new MyOrderCollection($resource);
    }
}
