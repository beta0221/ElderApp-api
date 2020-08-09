<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class OrderExport implements FromCollection,WithEvents,WithHeadings
{

    private $cellData;

    private $mergeColumn = ['A','B','F','G','H','I'];
    private $mergeArray;

    use Exportable;
    public function __construct($cellData,$cellDict)
    {
        $this->cellData = $cellData;
        $this->genMergeArray($cellDict);
    }

    private function genMergeArray($cellDict){
        $mergeArray = [];
        foreach ($cellDict as $key => $value) {
            if(!isset($cellDict[$key]['to'])){ continue; }
            $from = $cellDict[$key]['from'];
            $to = $cellDict[$key]['to'];
            foreach ($this->mergeColumn as $column) {
                $mergeArray[] = $column . $from . ':' . $column . $to;
            }
        }
        $this->mergeArray = $mergeArray;
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
            '訂購日期',//A
            '訂單編號',//B
            '產品',
            '總數量',
            '待收款',
            '收件人',//F
            '聯絡電話',//G
            '郵遞區號',//H
            '地址'//I
        ];
    }
}
