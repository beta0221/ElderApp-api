<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GroupMembers implements FromCollection,WithHeadings
{
    private $cellData;
    public function __construct($cellData){
        foreach ($cellData as $index => $row) {
            $no = $index+1;
            $status = '待付費';
            if($row->valid){ $status = '有效'; }
            $this->cellData[] = [
                $no,
                $row->name,
                $row->phone,
                $row->tel,
                $row->inviter,
                $row->expiry_date,
                $status,
            ];
        }
    }

    public function headings(): array
    {
        return [
            'NO.',
            '姓名',
            '手機',
            '電話',
            '推薦人',
            '到期日',
            '狀態',
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->cellData);
    }

    
}
