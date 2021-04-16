<?php

namespace App\Http\Controllers;

use App\Helpers\InventoryAction;
use App\Helpers\Pagination;
use App\Helpers\Tracker;
use App\Http\Resources\ProductDetailResource;
use App\Inventory;
use App\Location;
use App\Order;
use App\OrderDetail;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocationController extends Controller
{

    public function __construct()
    {
        $this->middleware(['JWT','admin'],[
            'only'=>[
                'locationList',
                'api_productList',
            ]
        ]);

        $this->middleware(['webAuth','role:location_manager'], ['only' => ['view_myLocation','view_locationOrderList','view_locationOrderDetail','view_nextStatus']]);

        
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

    /** X據點上架在兌換區的所有商品 view */
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

    /** X據點上架在兌換區&商城的所有商品 api */
    public function api_productList($slug){
        if(!$location = Location::where('slug',$slug)->first()){
            return abort(404);
        }

        $quantityDict = $location->getProductQuantityDict();

        $products = $location->products()->get();
        foreach ($products as $product) {
            $product->quantity = $quantityDict[$product->id]['quantity'];
            $product->quantity_cash = $quantityDict[$product->id]['quantity_cash'];
        }

        return response($products);
    }

    /** X據點的Y商品兌換清單 */
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

    /** X據點的Y商品已領取清單 */
    public function view_receiveList(Request $request,$location_slug,$product_slug){
        $location = Location::where('slug',$location_slug)->firstOrFail();
        $product = Product::where('slug',$product_slug)->firstOrFail();
        $p = new Pagination($request);

        $query = OrderDetail::where('location_id',$location->id)
            ->where('product_id',$product->id)
            ->where('receive',1);

        $total = $query->count();
        $p->cacuTotalPage($total);

        $query = $query->orderBy($p->orderBy, $p->ascOrdesc)
            ->skip($p->skip)
            ->take($p->rows);

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

        return view('location.receiveList',[
            'location'=>$location,
            'product'=>$product,
            'orders'=>$orders,
            'userDict'=>$userDict,
            'pagination'=>$p,
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
        Tracker::log($request);

        $this->validateRequest($request);
        
        $location = Location::findOrFail($request->id);
        $location->name = $request->name;
        $location->address = $request->address;
        $location->link = $request->link;
        $location->save();

        return response('success');
    }

    public function insertLocation(Request $request){
        Tracker::log($request);

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
        Tracker::log($request);
        
        $user = User::findOrFail($request->user_id);
        $location = Location::where('slug',$slug)->firstOrFail();

        if(!$result = $user->becomeRole('location_manager')){
            return response('error',400);
        }

        if(!$manager = $location->managers()->find($user->id)){
            $location->managers()->attach($user->id);
        }

        return response('success');
    }

    public function removeManager(Request $request,$slug){
        Tracker::log($request);
        
        $user = User::findOrFail($request->user_id);
        $location = Location::where('slug',$slug)->firstOrFail();

        $location->managers()->detach($user->id);

        if(count($user->locations()->get()) == 0){
            $user->removeLocationManagerRole();
        }

        return response('success');
    }

    public function view_myLocation(Request $request){
        if($request->token){
            Cookie::queue('token',$request->token,60);
        }
        $user = $request->user();
        $locations = $user->locations()->get();

        return view('location.myLocation',[
            'locations'=>$locations
        ]);
    }

    public function view_locationOrderList(Request $request,$slug){
        $user = request()->user();
        $p = new Pagination($request);
        $location = Location::where('slug',$slug)->firstOrFail();
        if(!$result = $location->managers()->find($user->id)){
            return abort(403, "Fail");
        }

        $query = Order::where('location_id',$location->id);
        $total = $query->count();
        $orderList = $query->skip($p->skip)->take($p->rows)->orderBy($p->orderBy,'desc')->get();
        
        $user_id_array = [];
        foreach ($orderList as $order) {
            if(!in_array($order->user_id,$user_id_array)){$user_id_array[] = $order->user_id;}
        }

        $users = User::select(['id','name'])->whereIn('id',$user_id_array)->get();
        $userDict = [];
        foreach ($users as $user) {
            $userDict[$user->id] = $user->name;
        }

        $orderList = Order::groupOrdersByNumero($orderList);
        $p->cacuTotalPage($total);

        return view('location.locationOrderList',[
            'pagination'=>$p,
            'orderList'=>$orderList,
            'userDict'=>$userDict,
            'slug'=>$slug,
        ]);
    }

    public function view_locationOrderDetail($slug,$order_numero){

        $user = request()->user();
        
        $location = Location::where('slug',$slug)->firstOrFail();
        if(!$result = $location->managers()->find($user->id)){
            return abort(403, "Fail");
        }
        
        $orders = Order::where('order_numero',$order_numero)->where('location_id',$location->id)->get();
        

        $productIdArray = [];
        foreach ($orders as $order) {
            $productIdArray[] = $order->product_id;
        }
        $productImageDict = Product::getProductImageDict($productIdArray);


        return view('location.locationOrderDetail',[
            'orders'=>$orders,
            'slug'=>$slug,
            'productImageDict'=>$productImageDict,
        ]);
    }

    public function view_nextStatus($slug,$order_numero){
        
        $user = request()->user();
        $location = Location::where('slug',$slug)->firstOrFail();
        if(!$result = $location->managers()->find($user->id)){
            return abort(403, "Fail");
        }

        $orders = Order::where('order_numero',$order_numero)->where('location_id',$location->id)->get();
        foreach ($orders as $order) {
            if($order->ship_status == Order::STATUS_VOID){ continue; }
            
            $order->ship_status = Order::STATUS_CLOSE;
            $order->save();
        }

        return redirect("/view_locationOrderList/$slug");
    }


    public function updateInventory(Request $request){
        Tracker::log($request);
        
        $inventoryAction = new InventoryAction($request);
        Inventory::updateInventory($inventoryAction);
        
        return response('success');

    }

    public function getInventory(Request $request,$location_id,$product_id){
        $location = Location::findOrFail($location_id);
        $product = Product::findOrFail($product_id);
        if(!$quantityRow = $product->getQuantity($location_id)){ return response('error',500); }

        $p = new Pagination($request);

        $query = Inventory::where('location_id',$location_id)
            ->where('product_id',$product_id);

        $p->cacuTotalPage($query->count());

        $inventoryLog = $query->skip($p->skip)->take($p->rows)->orderBy($p->orderBy,$p->ascOrdesc)->get();

        return response([
            'location' => $location,
            'product' => new ProductDetailResource($product),
            'quantityRow' => $quantityRow,
            'pagination'=> $p,
            'inventoryLog' =>$inventoryLog,
        ]);
    }
    
}
