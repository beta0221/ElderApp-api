<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    /**
     * 回朔此筆交易紀錄
     */
    public function reverse(){

        //系統交易才能回朔
        if($this->target_id != 0){
            return;
        }

        $give_take = !$this->give_take;
        $user = User::find($this->user_id);
        $user->updateWallet($give_take,$this->amount);

        $this->delete();

    }
    
}
