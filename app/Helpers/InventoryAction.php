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

    public static function getInstance($location_id,$product_id,$target,$action,$quantity,$comment){
        $req = new Request();
        $req->merge([
            'location_id'=> $location_id,
            'product_id'=> $product_id,
            'target'=> $target,
            'action'=> $action,
            'quantity'=> $quantity,
            'comment'=> $comment,
        ]);
        $inv = new static($req);
        return $inv;
    }

}