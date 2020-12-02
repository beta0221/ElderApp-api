<?php
namespace App\Helpers;

use Illuminate\Http\Request;

class Pagination{

    public $page = 1;
    public $rows = 10;
    public $skip = 0;
    public $ascOrdesc = 'desc';
    public $orderBy = 'id';
    //

    public $total = 0;
    public $hasNextPage = true;
    public $totalPage = 0;

    public function __construct(Request $request)
    {
        if($request->page){
            $this->page = $request->page;
        }
        if($request->rowsPerPage){
            $this->rows = $request->rowsPerPage;
        }
        if($request->descending == null || $request->descending == 'false'){
            $this->ascOrdesc = 'asc';
        }
        if($request->sortBy){
            $this->orderBy = $request->sortBy;
        }

        $this->skip = ($this->page - 1) * $this->rows;
    }

    public function setRows(int $rows){
        $this->rows = $rows;
        $this->skip = ($this->page - 1) * $this->rows;
    }


    /**
     * 計算總頁數
     * @param int $total
     * @return Void
     */
    public function cacuTotalPage(int $total){
        $this->total = $total;
        if(($this->skip + $this->rows) >= $total){
            $this->hasNextPage = false;
        }
        $this->totalPage = ceil($total / $this->rows);
    }
}