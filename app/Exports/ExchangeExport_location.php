<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExchangeExport_location implements FromCollection,WithHeadings
{

    private $cellData;

    public function __construct($cellData,$userNameDict,$productNameDict)
    {
        foreach ($cellData as $index => $row) {
            if(!isset($userNameDict[$row->user_id])){ continue; }
            if(!isset($productNameDict[$row->product_id])){ continue; }
            
            $no = $index+1;
            $this->cellData[] = [
                $no,
                $userNameDict[$row->user_id],
                $productNameDict[$row->product_id],
                ($row->receive)?'是':'否',
                $row->created_at->format('Y-m-d'),
            ];
        }
    }



    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->cellData);
    }



    public function headings(): array
    {
        return [
            'NO.',
            '姓名',
            '產品',
            '領取',
            '日期',
        ];
    }
}
