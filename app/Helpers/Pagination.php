<?php
namespace App\Helpers;

use Illuminate\Http\Request;

class Pagination{

    public $page = 1;
    public $rows = 15;
    public $skip = 0;
    public $ascOrdesc = 'desc';
    public $orderBy = 'id';

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

        $this->skip = ($this->page - 1) * $this->rows;
    }
}