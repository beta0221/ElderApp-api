<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderExport implements FromCollection
{

    private $cellData;
    use Exportable;
    public function __construct($cellData)
    {
        $this->cellData = $cellData;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->cellData);
    }

    // public function headings(): array
    // {
    //     return [
    //         'Name',
    //         'Surname',
    //         'Email',
    //         'Twitter',
    //     ];
    // }
}
