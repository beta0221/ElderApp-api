<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Exports\OrderExport;
use App\Exports\OrderExport_location;
use App\Helpers\Pagination;
use App\Helpers\Tracker;
use App\Location;
use App\Order;
use App\OrderDelievery;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cookie;

class OrderController extends Controller
{

    public function __construct()
    {
        $this->middleware(['JWT','FirmAndAdmin'], ['only' => ['getOrders','getOrderDetail','nextStatus','groupNextStatus','voidOrder','excel_downloadOrderExcel']]);
        $this->middleware('webAuth',['only'=>['view_orderList','view_orderDetail']]);
    }


    //api route

    public function getOrders(Request $request){
        $p = new Pagination($request);
        
        $column = ($request->column) ? $request->column : null;
        $value = (isset($request->value)) ? $request->value : null;
        // $blurSearch = ($request->blurSearch) ? true : false;
        $user = request()->user();
        
        $query = new Order();
        if(!$user->hasRole('admin')){
            $query = Order::where('firm_id',$user->id);
        }
        
        if($column != null && $value != null){
            if($column == 'created_at'){
                $query = $query->whereDate($column,date('Y-m-d',strtotime($value)));
            }else{
                $query = $query->where($column,$value);
            }
        }
        $total = $query->count();
        $query = $query->skip($p->skip)->take($p->rows)->orderBy('id',$p->ascOrdesc);
        $user_id_array = $query->pluck('user_id');
        $orders = $query->get();

        $userDict = User::getNameDictByIdArray($user_id_array);
        $orderList = Order::groupOrdersByNumero($orders);

        return response([
            'orderList'=>$orderList,
            'total'=>$total,
            'userDict'=>$userDict
        ]);
    }

    /**
     * 訂單詳情
     */
    public function getOrderDetail($order_numero){

        $user = request()->user();
        $firm_id = $user->id;

        $query = Order::where('order_numero',$order_numero);
        if(!$user->isAdmin()){  //如果不是管理員
            $query->where('firm_id',$firm_id);
        }

        if(!$orders = $query->get()){
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
        Tracker::log($request);
        
        $this->validate($request,[
            'order_numero'=>'required',
        ]);
        $user = request()->user();
        if(!$user->hasRole('firm')){
            return response('此操作身份必須為"廠商"',403);
        }

        $result = Order::updateToNextStatus($user->id,$request->order_numero);

        if($result == -1){
            return response(['s'=>0,'m'=>'已結案']);
        }else if($result == 0){
            return response(['s'=>0,'m'=>'系統錯誤']);
        }

        return response(['s'=>1,'m'=>'更新成功']);
    }

    /**作廢訂單 */
    public function voidOrder(Request $request,$order_numero){
        $query = Order::where('order_numero',$order_numero);
        $user = request()->user();
        
        if(!$user->isAdmin()){  //如果不是管理員
            $query->where('firm_id',$user->id);
        }

        if(!$orders = $query->get()){
            return response('error',500);
        }

        foreach ($orders as $order) {
            $order->voidOrder();
        }

        return response('success');
    }

    /**
     * 更新訂單狀態到下一個階段（批次更新）
     */
    public function groupNextStatus(Request $request){
        Tracker::log($request);
        
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

    public function excel_locationOrderExcel(Request $req){
        
        $location = Location::where('slug',$req->location_slug)->firstOrFail();
        $fileName = '訂單資料-' . $location->name;
        $query = Order::where('location_id',$location->id)
            ->where('ship_status',Order::STATUS_CLOSE)
            ->whereBetween('created_at',[date($req->from_date),date($req->to_date)]);

        if($req->has('product_id')){
            $product = Product::findOrFail($req->product_id);
            $fileName .= '-' . $product->name;
            $query->where('product_id',$req->product_id);
        }
            
        $user_id_array = $query->pluck('user_id');
        $orderList = $query->get();

        if(!count($orderList)){ return response('此塞選條件查無資料。');}

        $nameDict = User::getNameDictByIdArray($user_id_array);

        return Excel::download(new OrderExport_location($orderList,$nameDict),$fileName .'.xlsx');

    }

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

    public function view_thankyou($order_numero_array){

        $order_numero_array = explode(',',$order_numero_array);
        if(empty($order_numero_array)){
            return abort(404);
        }
        return view('order.thankyou',[
            'order_numero_array'=>$order_numero_array
        ]);
    }

    public function view_orderDetail(Request $req,$order_numero){
        
        if($req->token){
            Cookie::queue('token',$req->token,60);
        }

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
            'noFooter'=>$req->noFooter
        ]);

    }
    public function view_orderList(Request $req){

        if($req->token){
            Cookie::queue('token',$req->token,60);
        }
        
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

        $orderList = Order::groupOrdersByNumero($orders);
        
        return view('order.list',[
            'productImageDict'=>$productImageDict,
            'orderList'=>$orderList,
            'orders'=>$orders,
            'totalPage'=>$totalPage,
            'page'=>$page,
            'noFooter'=>$req->noFooter
        ]);
    }
    
}
