<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDelievery;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use stdClass;

class OrderController extends Controller
{

    public function __construct()
    {
        $this->middleware(['JWT','FirmAndAdmin'], ['only' => ['getOrders','nextStatus','groupNextStatus']]);
    }

    private function groupOrdersByNumero($orders){
        $_dict = [];
        $orderList = [];
        foreach ($orders as $order) {
            if(!isset($_dict[$order->order_numero])){
                $_order = new stdClass();
                $_order->created_at = $order->created_at;
                $_order->order_numero = $order->order_numero;
                $_order->ship_status = $order->ship_status;
                $_order->list = [];
                $orderList[] = $_order;
                $_dict[$order->order_numero] = count($orderList) - 1;
            }
        }
        foreach ($orders as $order) {
            $index = $_dict[$order->order_numero];
            $orderList[$index]->list[] = $order;
        }
        return $orderList;
    }

    //api route

    public function getOrders(Request $request){
        $page = ($request->page)?$request->page:1;
        $rows = ($request->rowsPerPage)?$request->rowsPerPage:15;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';
        if ($request->descending == null || $request->descending == 'false') {
            $ascOrdesc = 'asc';
        }
        // $orderBy = ($request->sortBy) ? $request->sortBy : 'id';
        // $column = ($request->column) ? $request->column : null;
        // $value = (isset($request->value)) ? $request->value : null;
        // $blurSearch = ($request->blurSearch) ? true : false;
        $firm_id = Auth::user()->id;
        
        $query = Order::where('firm_id',$firm_id);
        $total = $query->count();
        $orders = $query->skip($skip)->take($rows)->get();

        $orderList = $this->groupOrdersByNumero($orders);

        return response([
            'orderList'=>$orderList,
            'total'=>$total,
        ]);
    }

    /**
     * 更新訂單狀態到下一個階段（單一訂單號碼）
     */
    public function nextStatus(Request $request){
        $this->validate($request,[
            'order_numero'=>'required',
        ]);
        $firm_id = Auth::user()->id;
        
        $result = Order::updateToNextStatus($firm_id,$request->order_numero);

        if($result == -1){
            return response(['s'=>0,'m'=>'已結案']);
        }else if($result == 0){
            return response(['s'=>0,'m'=>'系統錯誤']);
        }

        return response(['s'=>1,'m'=>'更新成功']);
    }

    /**
     * 更新訂單狀態到下一個階段（批次更新）
     */
    public function groupNextStatus(Request $request){
        $this->validate($request,[
            'order_numero_array'=>'required',
        ]);
        $firm_id = Auth::user()->id;
        $order_numero_array = json_decode($request->order_numero_array,true);

        foreach ($order_numero_array as $order_numero) {
            Order::updateToNextStatus($firm_id,$order_numero);
        }
        return response('success');
    }

    //web route

    public function view_thankyou($order_numero){
        return view('order.thankyou',[
            'order_numero'=>$order_numero
        ]);
    }

    public function view_orderDetail($order_numero){
        
        if(!$orders = Order::where('order_numero',$order_numero)->get()){
            return view('errors.404');
        }

        $productIdArray = [];
        foreach ($orders as $order) {
            $productIdArray[] = $order->product_id;
        }
        $productImageDict = Product::getProductImageDict($productIdArray);

        if(!$orderDelievery = OrderDelievery::find($orders[0]->order_delievery_id)){
            return view('errors.404');
        }

        return view('order.detail',[
            'shipStatusDict'=>Order::$shipStatusDict,
            'productImageDict'=>$productImageDict,
            'order_numero'=>$order_numero,
            'orders'=>$orders,
            'orderDelievery'=>$orderDelievery
        ]);

    }
    public function view_orderList(){
        if(!$orders=Order::where('user_id',2)->get()){
            return view('errors.404');
        }

        $productIdArray = [];
        foreach ($orders as $order) {
            $productIdArray[] = $order->product_id;
        }
        $productImageDict = Product::getProductImageDict($productIdArray);

        $orderList = $this->groupOrdersByNumero($orders);
        
        return view('order.list',[
            'productImageDict'=>$productImageDict,
            'orderList'=>$orderList,
            'orders'=>$orders
        ]);
    }
    
}
