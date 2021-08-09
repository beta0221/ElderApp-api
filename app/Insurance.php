<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_VERIFIED = 'verified';
    const STATUS_CLOSE = 'close';
    const STATUS_VOID = 'void';

    protected $guarded=[];

    public function user(){
        return $this->belongsTo('App\User');
    }

    /**下階段狀態 */
    public function nextStatus(){
        switch ($this->status) {
            case static::STATUS_PENDING:
                $this->status = static::STATUS_PROCESSING;
                break;
            case static::STATUS_PROCESSING:
                $this->status = static::STATUS_VERIFIED;
                break;
            case static::STATUS_VERIFIED:
                $this->status = static::STATUS_CLOSE;
                break;
            default:
                break;
        }
        $this->save();
    }

    /**作廢 */
    public function void(){
        $this->status = static::STATUS_VOID;
        $this->save();
    }

    /**保險生效 */
    public function issue($date){
        $this->user->insurance_date = $date;
        $this->user->save();
    }




}
