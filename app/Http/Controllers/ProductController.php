<?php

namespace App\Http\Controllers;

use App\Helpers\ControllerTrait;
use App\Helpers\ImageResizer;
use App\Helpers\InventoryAction;
use App\Http\Resources\LocationQuantityCollection;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductListResource_User;
use App\OrderDetail;
use App\Product;
use App\Location;
use App\ProductCategory;
use App\Transaction;
use App\Helpers\Pagination;
use App\Helpers\Tracker;
use App\Inventory;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cookie;

class ProductController extends Controller
{
    use ControllerTrait;

    public function __construct()
    {
        $this->middleware(['JWT','FirmAndAdmin'], ['only' => ['index','store','destroy','update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $p = new Pagination($request);

        $query = DB::table('products')
        ->select('*');

        $user = request()->user();
        if($user->hasRole('firm')){
            $query = $query->where('firm_id',$user->id);
        }
        if($request->has('public')){
            $query = $query->where('public','&',$request->public);
        }

        $total = $query->count();
        $products = $query->orderBy('order_weight','desc')
        ->orderBy($p->orderBy, $p->ascOrdesc)
        ->skip($p->skip)
        ->take($p->rows)
        ->get();

        $products = ProductListResource::collection($products);

        return response()->json([
            'products' => $products,
            'total' => $total,
        ]);
    }
   

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Tracker::log($request);

        $this->validate($request,[
            'name'=>'required',
            'product_category_id'=>'required',
            'price'=>'required|min:1|integer',
            'file'=>'sometimes|nullable|image',
        ]);
        
        if($request->exchange_max == null || $request->exchange_max == 'null'){
            $request->request->remove('exchange_max');
        }
        
        $slug = 'P'.time();
        $request->merge(['slug'=>$slug]);
        if($request->hasFile('file')){
            $filename = $this->imageHandler($request->file('file'),$slug);
            
            if($filename){
                $request->merge(['img'=>$filename]);
            }else{
                return response('檔案無法儲存',500);
            }   
        }


        $request->merge(['firm_id'=>Auth::user()->id]);
        $request->merge(['info'=>$this->resizeHtmlImg($request->info)]);
        try {
            $product = Product::create($request->except('file','select_location','quantity','quantity_cash'));
        } catch (\Throwable $th) {
            return response($th,500);
        }

        if(!empty($request->select_location)){
            $location = explode(",",$request->select_location);
            $product->locations()->sync($location);
            
            if(is_array($quantity = json_decode($request->quantity,true))){
                foreach ($quantity as $key => $value) {
                    $product->updateQuantity((int)$key,(int)$value);
                }
            }
    
            if(is_array($quantity_cash = json_decode($request->quantity_cash,true))){
                foreach ($quantity_cash as $key => $value) {
                    $product->updateQuantityCash((int)$key,(int)$value);
                }
            }

        }
        

        return response($request,200);

    }

    /**
     * 舊的api, app更新後要移除
     */
    public function show(Product $product)
    {
        $location = $product->getLocationAndQuantity();
        $product['location'] = $location;
        return response($product);
    }
    /**App users 商品 detail */
    public function showV2($slug){

        $product = Product::where('slug',$slug)->firstOrFail();
        $product = new ProductDetailResource($product);

        $locationList = $product->getLocationAndQuantity();
        
        $locationIdArray = [];
        foreach ($locationList as $location) {
            $locationIdArray[] = $location->location_id;
        }

        $locationDict = Location::getLocationDict($locationIdArray);

        $locationList = new LocationQuantityCollection($locationList);
        $locationList = $locationList->configureDict($locationDict);

        return response([
            'product'=>$product,
            'locationList'=>$locationList
        ]);

    }

    /**後台用 */
    public function productDetail($slug){
        $product = Product::where('slug',$slug)->firstOrFail();
        $location = $product->getLocationAndQuantity();
        return response([
            'product'=> new ProductDetailResource($product),
            'location'=>$location
        ]);
    }

    public function getLocationAndQuantity($slug){
        $product = Product::where('slug',$slug)->firstOrFail();
        $location = $product->getLocationAndQuantity();
        return response($location);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        Tracker::log($request);
        
        if($request->hasFile('file')){
            $filename = $this->imageHandler($request->file('file'),$product->slug);
            if($filename){
                $request->merge(['img'=>$filename]);
            }else{
                return response('系統錯誤',500);
            }
        }
        
        if($request->exchange_max == null || $request->exchange_max == 'null'){
            $request->request->remove('exchange_max');
        }

        $request->merge(['info'=>$this->resizeHtmlImg($request->info)]);
        try {
            $product->update($request->except('file','select_location','quantity','quantity_cash'));
        } catch (\Throwable $th) {
            return response($th,500);
        }

        if(!empty($request->select_location)){
            $location = explode(",",$request->select_location);
            $product->locations()->sync($location);
        }else{
            $product->locations()->sync([]);
        }

        if(is_array($quantity = json_decode($request->quantity,true))){
            foreach ($quantity as $key => $value) {
                $product->updateQuantity((int)$key,(int)$value);
            }
        }

        if(is_array($quantity_cash = json_decode($request->quantity_cash,true))){
            foreach ($quantity_cash as $key => $value) {
                $product->updateQuantityCash((int)$key,(int)$value);
            }
        }

        return response('success',200);
        
    }

    /**更新產品排序權重 */
    public function updateOrderWeight(Request $request){
        $product = Product::findOrFail($request->product_id);
        $product->order_weight = $request->order_weight;
        $product->save();
        return response('success', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }


    //--------------------------------------------------------------------------

    public function productCategory(){
        return response(ProductCategory::all());
    }

    /**
     * 存入圖檔(目前在導入file sever 階段 必須同時執行 本地 以及 file sever 處理速度會很慢，等舊版本App更新才能完全轉移)
     */
    private function imageHandler($file,$product_slug){
        
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = "/images/products/" . $product_slug . "/";
        $ftpPath = "/products/$product_slug/";
        
        // if(Storage::disk('local')->exists($path)){
        //     $result = Storage::deleteDirectory($path);
        //     if(!$result){
        //         return false;
        //     }
        // }
        if(Storage::disk('ftp')->exists($ftpPath)){
            $result = Storage::disk('ftp')->deleteDirectory($ftpPath);
            if(!$result){
                return false;
            }
        }

        $img = ImageResizer::aspectFit($file,400)->encode();
        // if(!Storage::disk('local')->put($path . $filename,$img)){
        //     return false;//失敗:回傳false
        // }
        if(!Storage::disk('ftp')->put($ftpPath . $filename,$img)){
            return false;
        }
        
        return $filename;//成功：回傳檔名
    }

    public function purchase(Request $request, $product_slug){

        $this->validate($request,[
            'location_id'=>'required',
        ]);

        if(!$product = Product::where('slug',$product_slug)->first()){
            return response('產品不存在',400);
        }

        //檢查user樂幣是否足夠
        $user = Auth::user();
        if($user->wallet < $product->price){
            return response('樂必餘額不足，無法兌換',400);
        }

        if($exchange_max = $product->exchange_max){
            $hasExchangedSum = $product->hasExchangedSumBy($user->id);
            if($exchange_max <= $hasExchangedSum){
                return response("已達此商品兌換上限:$exchange_max",400);
            }
        }

        //檢查產品庫存 if 足夠 => 扣庫存
        if(!$product->isAvailable($request->location_id,1,Inventory::TARGET_GIFT)){
            return response('非常抱歉，此據點目前庫存已兌換完畢。',400);
        }
        $invAction = InventoryAction::getInstance($request->location_id,$product->id,Inventory::TARGET_GIFT,Inventory::ACTION_REMOVE,1,'系統-兌換商品');
        Inventory::updateInventory($invAction);

        //user 扣款
        $user->updateWallet(false,$product->price);
        
        //新增訂單購買紀錄
        OrderDetail::create([
            'user_id'=>$user->id,
            'product_id'=>$product->id,
            'location_id'=>$request->location_id,
        ]);

        //增加transaction 紀錄
        $tran_id = time() . rand(10,99);
        Transaction::create([
            'tran_id'=>$tran_id,
            'user_id'=>$user->id,
            'event' =>'兌換-'.$product->name,
            'amount'=>$product->price,
            'target_id'=>0,
            'give_take'=>false,
        ]);

        return response('success',200);

    }

    /**
     * 舊的api, app更新後要移除
     */
    public function getAllProduct(){
        
        $products = Product::where('public','&',5)->orderBy('id','desc')->get();
        $cats = ProductCategory::all();

        return response([
            'products'=>$products,
            'cats'=>$cats,
        ],200);

    }
    public function productList(Request $request){

        $page = ($request->page)?$request->page:1;
        $rows = 10;
        $skip = ($page - 1) * $rows;
        $ascOrdesc = 'desc';

        $total = Product::where('public','&',5)->count();
        $productList = Product::where('public','&',5)->skip($skip)->take($rows)->orderBy('order_weight','desc')->orderBy('id',$ascOrdesc)->get();
        $productList = ProductListResource_User::collection($productList);

        $hasNextPage = true;
        if(($skip + $rows) >= $total){ $hasNextPage = false; }

        return response([
            'productList'=>$productList,
            'hasNextPage'=>$hasNextPage
        ]);

    }

    /**銀髮商城 */
    public function list(Request $req){

        if($req->token){
            Cookie::queue('token',$req->token,60);
        }

        $p = new Pagination($req);
        $total = Product::where('public','&',6)->count();
        $p->cacuTotalPage($total);
        
        $products = Product::where('public','&',6)
            ->skip($p->skip)
            ->take($p->rows)
            ->orderBy('order_weight','desc')
            ->orderBy('id',$p->ascOrdesc)
            ->get();

        return view('product.list',[
            'products'=>$products,
            'totalPage'=>$p->totalPage,
            'page'=>$p->page,
        ]);
    }

    public function detail(Request $req,$slug){
        $product = Product::where('slug',$slug)->firstOrFail();

        if($req->token){
            Cookie::queue('token',$req->token,60);
        }

        return view('product.detail',[
            'product'=>$product,
        ]);
    }

    public function universal_link($slug){
        
        $product = Product::where('slug',$slug)->firstOrFail();

        return view('product.detail_link',[
            'product'=>$product
        ]);
    }

}
