<?php

namespace App\Http\Controllers;

use App\Location;
use App\OrderDetail;
use App\User;
use Illuminate\Http\Request;

class LocationController extends Controller
{

    public function __construct()
    {
        $this->middleware(['JWT','admin'],[
            'only'=>[
                'locationList',
            ]
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(Location::all(),200);
    }

    public function orderListPage($slug){

        if(!$location = Location::where('slug',$slug)->first()){
            return abort(404);
        }

        $_products = $location->products()->get();
        $products = [];
        foreach ($_products as $product) {
            if($product->public == 1){
                $products[] = $product;
            }
        }

        return view('location.orderList',[
            'location'=>$location,
            'products'=>$products,
        ]);
    }

    public function orderList($location_id,$product_id){

        $orders = OrderDetail::where('location_id',$location_id)->where('product_id',$product_id)->where('receive',0)->select(['id','user_id','product_id'])->get();
        foreach ($orders as $order) {
            if($user = User::where('id',$order->user_id)->select(['name','id_code'])->first()){
                $order['name'] = $user->name;
                $order['id_code'] = $user->id_code;
            }
        }

        return response($orders,200);

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
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function edit(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Location  $location
     * @return \Illuminate\Http\Response
     */
    public function destroy(Location $location)
    {
        //
    }

    public function locationList(Request $request){
        
        $page = ($request->page)?$request->page:1;
        $rows = ($request->rowsPerPage)?$request->rowsPerPage:15;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = ($request->descending == null || $request->descending == 'false')?'desc':'asc';
        $orderBy = ($request->sortBy) ? $request->sortBy : 'id';

    }




}
