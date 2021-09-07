<?php

namespace App\Http\Resources;

use App\Clinic;
use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicUserLogResource extends JsonResource
{


    protected $userName;
    protected $clinicName;


    public function setName($userName,$clinicName){
        $this->userName = $userName;
        $this->clinicName = $clinicName;
        return $this;
    }

    public function withName(){
        $this->userName = User::find($this->user_id)->name;
        $this->clinicName = Clinic::find($this->clinic_id)->name;
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
        return [
            'id' => $this->id,
            'clinic_id' => $this->clinic_id,
            'clinic_name' => $this->clinicName,
            'user_name' => $this->userName,
            'user_id' => $this->user_id,
            'is_complete' => $this->is_complete,
            'complete_at' => $this->complete_at,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'total_hours' => $this->total_hours
        ];
    }


    public static function collection($resource){
        return new ClinicUserLogCollection($resource);
    }


}
