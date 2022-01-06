<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ClinicUserSignatureExport implements FromCollection,WithEvents
{

    const Meal_fee = 80;
    const Org_fee = 20;
    const System_fee = 50;

    private $clinicUserLogList;
    private $cellData;
    private $nameDict = [];
    private $mergeArray = [];

    use Exportable;
    public function __construct($clinicUserLogList,$title)
    {
        $this->clinicUserLogList = $clinicUserLogList->sortBy('user_id');
        $this->setTitleRow($title);
        $this->genNameDict();
        $this->genCellData();
        
    }

    private function setTitleRow($title){
        $this->cellData[] = [
            $title
        ];
        $this->mergeArray[] = 'A1:D1';


        $this->cellData[] = [
            '志工姓名',//A
            '日期',//B
            '時數',//C
            '誤餐費'//D
        ];

    }

    private function genNameDict(){
        $userIdArray = $this->clinicUserLogList->pluck('user_id');
        $userNameList = User::whereIn('id',$userIdArray)->select(['id','name'])->get();
        foreach ($userNameList as $user) {
            $this->nameDict[$user->id] = $user->name;
        }
    }

    private function genCellData(){

        $user_id = null;
        $from = 3;
        $to = 3;
        $total_hours = 0;
        $total_meal_fee = 0;
        

        foreach ($this->clinicUserLogList as $i => $log) {

            if(!isset($this->nameDict[$log->user_id])){ continue; }
            
            
            if(!is_null($user_id) && $user_id != $log->user_id){    //上個人結束
                
                $this->insertTotalRow($total_hours,$total_meal_fee);
                $this->setMergeArray($from,$to);
                
                $total_hours = 0;
                $total_meal_fee = 0;
                
                $to += 2;
                $from = $to;
            }

            $to += 1;
            $name = $this->nameDict[$log->user_id];
            $dateTime = $log->created_at . ' ~ ' . $log->complete_at;
            $hours = $log->total_hours;

            $row = [
                $name,$dateTime,$hours,self::Meal_fee
            ];
            $this->cellData[] = $row;

            $user_id = $log->user_id;
            $total_hours += $log->total_hours;
            $total_meal_fee += self::Meal_fee;

            
        }

        $this->insertTotalRow($total_hours,$total_meal_fee);
        $this->setMergeArray($from,$to);

        

    }

    private function insertTotalRow($total_hours,$total_meal_fee){
        $this->cellData[] = [
            '總計：',null,$total_hours,$total_meal_fee
        ];
        $this->cellData[] = [
            '領取簽名'
        ];
        
    }

    private function setMergeArray($from,$to){
        $this->mergeArray[] = 'A' . $from . ':' . 'A' . ($to-1);
        $this->mergeArray[] = 'B' . ($to+1) . ':' . 'D' . ($to + 1);
    }

    

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->cellData);
    }


    public function registerEvents():array
    {
        return [
            AfterSheet::class => function(AfterSheet $event){
                $event->sheet->getDelegate()->setMergeCells($this->mergeArray);
            }
        ];
    }

}
