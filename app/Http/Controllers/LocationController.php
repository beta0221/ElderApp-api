<?php

namespace App\Http\Controllers;

use App\Location;
use App\OrderDetail;
use App\Product;
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

    public function view_productList($slug){

        if(!$location = Location::where('slug',$slug)->first()){
            return abort(404);
        }

        $products = $location->products()->where('public','&',5)->get();

        return view('location.productList',[
            'location'=>$location,
            'products'=>$products,
        ]);
    }


    public function view_orderList(Request $request,$location_slug,$product_slug){

        $location = Location::where('slug',$location_slug)->firstOrFail();
        $product = Product::where('slug',$product_slug)->firstOrFail();

        $query = OrderDetail::where('location_id',$location->id)
            ->where('product_id',$product->id)
            ->where('receive',0);

        $user_id_array = $query->pluck('user_id');
        $orders = $query->select(['id','user_id','product_id'])->get();

        $users = User::select(['id','name','id_code'])->whereIn('id',$user_id_array)->get();
        $userDict = [];
        foreach ($users as $user) {
            $userDict[$user->id] = $user;
        }

        foreach ($orders as $order) {
            $order->name = $userDict[$order->user_id]['name'];
            $order->id_code = $userDict[$order->user_id]['id_code'];
        }

        return view('location.orderList',[
            'location'=>$location,
            'product'=>$product,
            'orders'=>$orders,
            'userDict'=>$userDict,
            'total'=>count($orders),
        ]);
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

        $total = Location::count();
        $locationList = Location::skip($skip)->take($rows)->orderBy($orderBy,$ascOrdesc)->get();
        
        return response([
            'locationList'=>$locationList,
            'total'=>$total
        ]);

    }

    private function validateRequest(Request $request){
        $this->validate($request,[
            'name'=>'required',
            'address'=>'required',
            'link'=>'required',
        ]);
    }
    public function updateLocation(Request $request){
        $this->validateRequest($request);
        
        $location = Location::findOrFail($request->id);
        $location->name = $request->name;
        $location->address = $request->address;
        $location->link = $request->link;
        $location->save();

        return response('success');
    }

    public function insertLocation(Request $request){
        $this->validateRequest($request);
        
        $slug = 'L' . uniqid();
        $location = new Location();
        $location->slug = $slug;
        $location->user_id = auth()->user()->id;
        $location->name = $request->name;
        $location->address = $request->address;
        $location->link = $request->link;
        $location->save();

        return response('success');
    }



    public function getLocationManagers($slug){
        $location = Location::where('slug',$slug)->firstOrFail();
        $managers = $location->managers()->get();
        return response($managers);
    }

    public function addManager(Request $request,$slug){
        
        $user = User::findOrFail($request->user_id);
        $location = Location::where('slug',$slug)->firstOrFail();

        if(!$result = $user->becomeRole('location_manager')){
            return response('error',400);
        }

        $location->managers()->attach($user->id);

        return response('success');
    }

    public function removeManager(Request $request,$slug){
        
        $user = User::findOrFail($request->user_id);
        $location = Location::where('slug',$slug)->firstOrFail();

        $location->managers()->detach($user->id);

        if(count($user->locations()->get()) == 0){
            $user->removeLocationManagerRole();
        }

        return response('success');
    }


}
