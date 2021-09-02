<?php

namespace App\Http\Resources;

use App\Clinic;
use App\User;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClinicUserLogCollection extends ResourceCollection
{

    protected $userDict;
    protected $clinicDict;

    public function __construct($resource)
    {
        parent::__construct($resource);

        $user_id_array = [];
        $clinic_id_array = [];
        foreach ($resource as $res) {
            $user_id_array[] = $res->user_id;
            $clinic_id_array[] = $res->clinic_id;
        }

        $this->userDict = User::getNameDictByIdArray($user_id_array);
        $this->clinicDict = Clinic::getDictByIdArray($clinic_id_array,'name');

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
            if(!isset($this->userDict[$res->user_id]) || !isset($this->clinicDict[$res->clinic_id])){
                return;
            }

            $clinicUserLogResource = new ClinicUserLogResource($res);
            return $clinicUserLogResource->setName($this->userDict[$res->user_id],$this->clinicDict[$res->clinic_id])->toArray($request);

        });
    }
}
