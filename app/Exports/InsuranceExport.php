<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;

class InsuranceExport implements FromCollection,WithHeadings
{

    private $insuranceList;
    private $nameDict;

    use Exportable;
    public function __construct($insuranceList,$nameDict)
    {
        $this->insuranceList = $insuranceList;
        $this->nameDict = $nameDict;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $data = [];
        foreach ($this->insuranceList as $insurance) {
            if(!isset($this->nameDict[$insurance->user_id])){ continue; }

            $row = [
                $this->nameDict[$insurance->user_id],
                $insurance->name,
                $insurance->identity_number,
                $insurance->birthdate,
                $insurance->phone,
                $insurance->occupation,
                $insurance->relation,
                750,
                ($insurance->q_1)?'是':'否',
                ($insurance->q_2)?'是':'否',
                ($insurance->q_3)?'是':'否',
                ($insurance->q_4)?'是':'否',
                ($insurance->q_5)?'是':'否',
                $insurance->description,
                $insurance->created_at->format('Y-m-d'),
            ];
            $data[] = $row;
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            '會員姓名',
            '被保險人姓名',
            '身分證字號',
            '生日',
            '聯絡電話',
            '工作內容',
            '與會員關係',
            '投保金額',
            '一',
            '二',
            '三',
            '四',
            '五',
            '備註',
            '聲明日期'
        ];
    }

}
