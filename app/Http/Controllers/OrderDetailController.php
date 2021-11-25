<?php

namespace App\Http\Controllers;

use App\Helpers\InventoryAction;
use App\Helpers\Tracker;
use App\Http\Resources\MyOrderCollection;
use App\Http\Resources\ProductOrderCollection;
use App\Inventory;
use App\OrderDetail;
use App\Product;
use App\Location;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderDetailController extends Controller
{

    public function __construct()
    {
        $this->middleware(['JWT','FirmAndAdmin'], ['only' => ['productOrderList','deleteOrderDetail']]);
        $this->middleware('JWT', ['only' => ['myOrderListV2']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('order.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\OrderDetail  $orderDetail
     * @return \Illuminate\Http\Response
     */
    public function show($order_id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OrderDetail  $orderDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderDetail $orderDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\OrderDetail  $orderDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderDetail $orderDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\OrderDetail  $orderDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderDetail $orderDetail)
    {
        //
    }

    public function view_userOrder(Request $request,$id_code){
        
        $user = User::where('id_code',$id_code)->firstOrFail();
        
        $query = OrderDetail::where('user_id',$user->id)->where('receive',0);
        $productIdArray = $query->pluck('product_id');
        $locationIdArray = $query->pluck('location_id');
        $orderList = $query->get();

        // return response()->json($orderList);

        $productDict = Product::getProductDict($productIdArray);
        $locationDict = Location::getLocationDict($locationIdArray);
        
        $orderList = new MyOrderCollection($orderList);
        $orderList = $orderList->configureDict($productDict,$locationDict);
        $orderList = $orderList->toArray($request);

        return view('order.userOrder',[
            'user' => $user,
            'orderList' => $orderList
        ]);

    }

    public function receiveOrder(Request $req){
        
        $this->validate($req,[
            'id'=>'required',
            'user_id'=>'required',
            'product_id'=>'required',
            'location_id'=>'required',
        ]);

        // $id,$user_id,$product_id,$location_id
        $row = OrderDetail::where('id',$req->id)->where('user_id',$req->user_id)->where('product_id',$req->product_id)->where('location_id',$req->location_id)->firstOrFail();
        $row->receive = !$row->receive;
        $row->save();
        
        return response()->json('success');
    }

    public function myOrderList(){

        if(!$user = Auth::user()){
            return response('使用者權限錯誤',400);
        }

        $productDic = Product::getProductDictionary_abandoned();
        $locationDic = Location::getLocationDictionary_abandoned();

        $orders = OrderDetail::where('user_id',$user->id)->orderBy('id','desc')->take(20)->get();
        foreach ($orders as $order) {
            if($p = $productDic[$order->product_id]){
                $order['product'] = $p['name'];
                $order['img'] =  $p['img'];
            }
            if($l = $locationDic[$order->location_id]){
                $order['location'] = $l['name'];
                $order['address'] = $l['address'];
            }
            unset($order['id']);
            unset($order['user_id']);
            unset($order['product_id']);
        }

        return response($orders,200);

    }

    public function myOrderListV2(Request $request){
        
        $page = ($request->page)?$request->page:1;
        $rows = 10;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';
        $user = auth()->user();

        $total = OrderDetail::where('user_id',$user->id)->count();
        $orderList = OrderDetail::where('user_id',$user->id)->skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get();
        
        $productIdArray = [];
        $locationIdArray = [];
        foreach ($orderList as $order) {
            $productIdArray[] = $order->product_id;
            $locationIdArray[] = $order->location_id;
        }

        $productDict = Product::getProductDict($productIdArray);
        $locationDict = Location::getLocationDict($locationIdArray);

        $orderList = new MyOrderCollection($orderList);
        $orderList = $orderList->configureDict($productDict,$locationDict);

        $hasNextPage = true;
        if(($skip + $rows) >= $total){ $hasNextPage = false; }

        return response([
            'orderList'=>$orderList,
            'hasNextPage'=>$hasNextPage
        ]);

    }

    /**
     * 某個商品的兌換紀錄
     */
    public function productOrderList(Request $request,$product_id){
        
        $page = ($request->page)?$request->page:1;
        $rows = 20;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';

        $total = OrderDetail::where('product_id',$product_id)->count();
        $orderList = OrderDetail::where('product_id',$product_id)->skip($skip)->take($rows)->orderBy('id',$ascOrdesc)->get();

        $userIdArray = [];
        $locationIdArray = [];
        foreach ($orderList as $order) {
            $userIdArray[] = $order->user_id;
            $locationIdArray[] = $order->location_id;
        }

        $locationDict = Location::getLocationDict($locationIdArray);
        $userNameDict = User::getNameDictByIdArray($userIdArray);

        $orderList = new ProductOrderCollection($orderList);
        $orderList = $orderList->configureDict($locationDict,$userNameDict);

        return response([
            'total'=>$total,
            'orderList'=>$orderList
        ]);

    }

    /**
     * 取消樂幣兌換的訂單
     */
    public function deleteOrderDetail(Request $request){
        Tracker::log($request);

        $this->validate($request,[
            'order_detail_id'=>'required'
        ]);

        $orderDetail = OrderDetail::findOrFail($request->order_detail_id);

        if($orderDetail->receive){
            return response(['s'=>0,'m'=>'此商品已領取無法取消']);
        }

        $product = Product::findOrFail($orderDetail->product_id);
        $invAction = InventoryAction::getInstance($orderDetail->location_id,$product->id,Inventory::TARGET_GIFT,Inventory::ACTION_ADD,1,'系統-取消兌換訂單');
        Inventory::updateInventory($invAction);
        
        $user = User::find($orderDetail->user_id);
        $user->update_wallet_with_trans(User::INCREASE_WALLET,$product->price,"取消兌換-" . $product->name);

        $orderDetail->delete();

        return response([
            's'=>1,
            'm'=>'success'
        ]);

    }

}
