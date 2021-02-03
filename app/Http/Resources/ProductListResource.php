<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
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
            'id'=>$this->id,
            'order_weight'=>$this->order_weight,
            'name'=>$this->name,
            'slug'=>$this->slug,
            'img'=>$this->img,
            'public'=>$this->public,
            //'public_text'=>($this->public)?'上架':'下架',
            'product_category_id'=>$this->product_category_id,
            'imgUrl'=>$imgUrl,
        ];
        
    }
}
