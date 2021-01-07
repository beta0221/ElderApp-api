<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderExport_location implements FromCollection,WithHeadings
{

    

    private $cellData;

    public function __construct($cellData,$nameDict)
    {

        foreach ($cellData as $index => $row) {
            $no = $index+1;
            $this->cellData[] = [
                $no,
                $nameDict[$row->user_id],
                $row->created_at,
                $row->name,
                $row->point_cash_quantity,
                $row->pay_cash_price,
                $row->total_cash,
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
            '訂單日期',
            '產品',
            '數量',
            '單價',
            '小記',
        ];
    }

}
