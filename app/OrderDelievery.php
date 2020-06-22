<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderDelievery extends Model
{
    protected $table = 'order_delievery';
    protected $guarded = [];

    /**
     * insert 進資料庫
     * @param Int user_id 
     * @param Request 除了user_id 以外的其他欄位
     * @return Int order_delievery_id
     */
    public static function insert_row($user_id,Request $request){
        $orderDelievery = OrderDelievery::create([
            'user_id'=>$user_id,
            'receiver_name'=>$request->receiver_name,
            'receiver_phone'=>$request->receiver_phone,
            'county'=>$request->county,
            'district'=>$request->district,
            'zipcode'=>$request->zipcode,
            'address'=>$request->address,
        ]);
        return $orderDelievery->id;
    }

}
