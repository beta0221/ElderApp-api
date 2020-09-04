<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
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
        $buynowUrl = config('app.url') . "/product/detail/$this->slug";
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'slug'=>$this->slug,
            'img'=>$this->img,
            'exchange_max'=>$this->exchange_max,
            'public'=>$this->public,
            'price'=>$this->price,
            'pay_cash_point'=>$this->pay_cash_point,
            'pay_cash_price'=>$this->pay_cash_price,
            'original_cash'=>$this->original_cash,
            'cash'=>$this->cash,
            'product_category_id'=>$this->product_category_id,
            'info'=>$this->info,
            'imgUrl'=>$imgUrl,
            'buynowUrl'=>$buynowUrl,
        ];
    }
}
