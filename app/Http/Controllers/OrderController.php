<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Exports\OrderExport;
use App\Location;
use App\Order;
use App\OrderDelievery;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class OrderController extends Controller
{

    public function __construct()
    {
        $this->middleware(['JWT','FirmAndAdmin'], ['only' => ['getOrders','getOrderDetail','nextStatus','groupNextStatus','excel_downloadOrderExcel']]);
        $this->middleware('webAuth',['only'=>['view_orderList','view_orderDetail']]);
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
        $column = ($request->column) ? $request->column : null;
        $value = (isset($request->value)) ? $request->value : null;
        // $blurSearch = ($request->blurSearch) ? true : false;
        $firm_id = Auth::user()->id;
        
        $query = Order::where('firm_id',$firm_id);
        if($column != null && $value != null){
            if($column == 'created_at'){
                $query->whereDate($column,date('Y-m-d',strtotime($value)));
            }else{
                $query->where($column,$value);
            }
        }
        $total = $query->count();
        $orders = $query->skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get();

        $orderList = $this->groupOrdersByNumero($orders);

        return response([
            'orderList'=>$orderList,
            'total'=>$total,
        ]);
    }

    /**
     * 訂單詳情
     */
    public function getOrderDetail($order_numero){
        $firm_id = Auth::user()->id;
        if(!$orders = Order::where('order_numero',$order_numero)->where('firm_id',$firm_id)->get()){
            return response('error',500);
        }

        $productIdArray = [];
        $locationArray = [];
        foreach ($orders as $order) {
            $productIdArray[] = $order->product_id;
            if($order->location_id){$locationArray[] = $order->location_id;}
        }
        $productImageDict = Product::getProductImageDict($productIdArray);


        $orderDelievery = null;
        $locationDict = [];

        if(!$orders[0]->order_delievery_id){
            $locationList = Location::whereIn('id',$locationArray)->get();
            foreach ($locationList as $location) {
                $locationDict[$location->id] = $location->name;
            }
        }else{
            if(!$orderDelievery = OrderDelievery::find($orders[0]->order_delievery_id)){
                return response('error',500);
            }
        }
        

        return response([
            'orders'=>$orders,
            'orderDelievery'=>$orderDelievery,
            'locationDict'=>$locationDict,
            'productImageDict'=>$productImageDict,
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

    public function excel_downloadOrderExcel(Request $request){
        $this->validate($request,[
            'order_numero_array'=>'required',
        ]);
        
        $firm_id = Auth::user()->id;
        $order_numero_array = explode(',',$request->order_numero_array);
        
        
        $orders = Order::whereIn('order_numero',$order_numero_array)->where('firm_id',$firm_id)->get();
        $cellData = [];
        $cellDict = [
            // 's232323'=>[
            //     'from'=>2,
            //     'to'=>5,
            // ],
        ];
        foreach ($orders as $index => $order) {
            $delivery = null;
            if(!isset($cellDict[$order->order_numero])){
                $cellDict[$order->order_numero] = ['from'=>$index+2];
                $delivery = OrderDelievery::find($order->order_delievery_id);
            }else{
                $cellDict[$order->order_numero]['to'] = $index+2;
            }
            $data = [
                $order->created_at,
                $order->order_numero,
                $order->name,
                $order->total_quantity,
                $order->total_cash,
            ];
            if($delivery){
                $data[] = $delivery->receiver_name;
                $data[] = $delivery->receiver_phone;
                $data[] = $delivery->zipcode;
                $data[] = $delivery->county . $delivery->district . $delivery->address;
            }
            $cellData[] = $data;
        }

        date_default_timezone_set('Asia/Taipei');
        $now = date("Y-m-d");

        return Excel::download(new OrderExport($cellData,$cellDict),'訂單資料-'.$now.'.xlsx');
        
    }

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
        $locationArray = [];
        foreach ($orders as $order) {
            $productIdArray[] = $order->product_id;
            if($order->location_id){$locationArray[] = $order->location_id;}
        }
        $productImageDict = Product::getProductImageDict($productIdArray);

        $orderDelievery = null;
        $locationDict = [];

        if(!$orders[0]->order_delievery_id){
            $locationList = Location::whereIn('id',$locationArray)->get();
            foreach ($locationList as $location) {
                $locationDict[$location->id] = $location;
            }
        }else{
            if(!$orderDelievery = OrderDelievery::find($orders[0]->order_delievery_id)){
                return view('errors.404');
            }
        }

        return view('order.detail',[
            'shipStatusDict'=>Order::$shipStatusDict,
            'productImageDict'=>$productImageDict,
            'order_numero'=>$order_numero,
            'orders'=>$orders,
            'orderDelievery'=>$orderDelievery,
            'locationDict'=>$locationDict,
        ]);

    }
    public function view_orderList(Request $req){

        $page = ($req->page)?$req->page:1;
        $rows = 6;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';

        $user_id = User::web_user()->id;

        $total = Order::where('user_id',$user_id)->count();
        if($rows > $total){
            $totalPage = 1;
        }else{
            $totalPage = floor($total / $rows);
            if($total % $rows != 0){ $totalPage += 1; }
        }
        
        if(!$orders=Order::where('user_id',$user_id)->skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get()){
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
            'orders'=>$orders,
            'totalPage'=>$totalPage,
            'page'=>$page,
        ]);
    }
    
}
