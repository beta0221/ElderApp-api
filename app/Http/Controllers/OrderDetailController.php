<?php

namespace App\Http\Controllers;

use App\OrderDetail;
use App\Product;
use App\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderDetailController extends Controller
{
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

    public function receiveOrder(Request $req){
        
        $this->validate($req,[
            'id'=>'required',
            'user_id'=>'required',
            'product_id'=>'required',
            'location_id'=>'required',
        ]);

        // $id,$user_id,$product_id,$location_id
        $row = OrderDetail::where('id',$req->id)->where('user_id',$req->user_id)->where('product_id',$req->product_id)->where('location_id',$req->location_id)->firstOrFail();
        $row->receive = true;
        $row->save();
        
        return response()->json('success');
    }

    public function myOrderList(){



        if(!$user = Auth::user()){
            return response('使用者權限錯誤',400);
        }

        $productDic = Product::getProductDictionary();
        $locationDic = Location::getLocationDictionary();

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

}
