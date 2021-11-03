<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GroupMembers implements FromCollection,WithHeadings
{
    private $cellData;
    private $orgRankDict = [
        1=>'主人',
        2=>'小天使',
        3=>'大天使',
        4=>'守護天使',
        5=>'領航天使',
    ];

    public function __construct($cellData){
        foreach ($cellData as $index => $row) {
            $no = $index+1;
            $status = '待付費';
            if($row->valid){ $status = '有效'; }
            $orgTitle = (isset($this->orgRankDict[$row->org_rank]))?$this->orgRankDict[$row->org_rank]:'主人';

            $this->cellData[] = [
                $no,
                $row->name,
                $row->phone,
                $row->tel,
                $row->inviter,
                $row->expiry_date,
                $status,
                $orgTitle
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
            '職位'
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
