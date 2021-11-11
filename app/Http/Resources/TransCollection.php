<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransCollection extends ResourceCollection
{
    private $nameDict = null;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $userIdArray = [];
        foreach ($resource as $row) {
            $userIdArray[] = $row->target_id;
        }

        $this->nameDict = User::getNameDictByIdArray($userIdArray);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->map(function($res) use($request){
            $name = '';

            if($res->target_id == 0){
                $name = "銀髮學院";
            }
            if(isset($this->nameDict[$res->target_id])){
                $name = $this->nameDict[$res->target_id];
            }

            $amount_char = '-';
            if($res->give_take == 1){
                $amount_char = '+';
            }

            return [
                'id' => $res->id,
                'event' => $res->event,
                'amount' => "$amount_char $res->amount",
                'give_take' => $res->give_take,
                'target_name' => $name,
                'created_at' => $res->created_at
            ];

        });
    }
}
