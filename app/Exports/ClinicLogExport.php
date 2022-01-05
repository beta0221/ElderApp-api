<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ClinicLogExport implements FromCollection,WithEvents,WithHeadings
{

    const Meal_fee = 80;
    const Org_fee = 20;
    const System_fee = 50;

    private $clinicUserLogList;
    private $cellData;
    private $nameDict = [];
    private $mergeColumn = ['A','B','F','G','H','I'];
    private $mergeArray = [];

    use Exportable;
    public function __construct($clinicUserLogList)
    {
        $this->clinicUserLogList = $clinicUserLogList->sortBy('user_id');
        $this->genNameDict();
        $this->genCellData();
        
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
        $from = 2;
        $to = 2;
        $total_hours = 0;
        $total_meal_fee = 0;
        $total_org_fee = 0;
        $total_system_fee = 0;

        foreach ($this->clinicUserLogList as $i => $log) {

            if(!isset($this->nameDict[$log->user_id])){ continue; }
            

            if(!is_null($user_id) && $user_id != $log->user_id){    //上個人結束
                $this->cellData[] = [
                    '加總',null,$total_hours,$total_meal_fee,$total_org_fee,$total_system_fee
                ];
                $total_hours = 0;
                $total_meal_fee = 0;
                $total_org_fee = 0;
                $total_system_fee = 0;
                
                $this->mergeArray[] = 'A' . $from . ':' . 'A' . ($to-1);
                $from = $to+1;
            }

            
            $name = $this->nameDict[$log->user_id];
            $dateTime = $log->created_at . ' ~ ' . $log->complete_at;
            $hours = $log->total_hours;

            $row = [
                $name,$dateTime,$hours,self::Meal_fee,self::Org_fee,self::System_fee
            ];
            $this->cellData[] = $row;

            $user_id = $log->user_id;
            $total_hours += $log->total_hours;
            $total_meal_fee += self::Meal_fee;
            $total_org_fee += self::Org_fee;
            $total_system_fee += self::System_fee;
            $to += 1;
            
        }

        $this->cellData[] = [
            '加總',null,$total_hours,$total_meal_fee,$total_org_fee,$total_system_fee
        ];
        $this->mergeArray[] = 'A' . $from . ':' . 'A' . $to;

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

    public function headings(): array
    {
        return [
            '志工姓名',//A
            '日期',//B
            '時數',//C
            '誤餐費',//D
            '銀協行政費',//E
            '系統及教育訓練費',//F
        ];
    }
}
