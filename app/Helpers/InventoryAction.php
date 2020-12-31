<?php
namespace App\Helpers;

use Illuminate\Http\Request;

class InventoryAction{

    public $location_id;
    public $product_id;
    public $target;
    public $action;
    public $quantity;
    public $comment;

    public function __construct(Request $request){
        $this->location_id = $request->location_id;
        $this->product_id = $request->product_id;
        $this->target = $request->target;
        $this->action = $request->action;
        $this->quantity = $request->quantity;
        $this->comment = $request->comment;
    }

}