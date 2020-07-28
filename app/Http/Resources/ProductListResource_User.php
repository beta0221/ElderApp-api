<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource_User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $imgUrl = null;
        if($this->img){
            $imgUrl = config('app.static_host') . "/products/$this->slug/$this->img";
        }
        return [
            'name'=>$this->name,
            'slug'=>$this->slug,
            'price'=>$this->price,
            'pay_cash_price'=>$this->pay_cash_price,
            'imgUrl'=>$imgUrl,
        ];
    }
}
