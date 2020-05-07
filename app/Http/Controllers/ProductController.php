<?php

namespace App\Http\Controllers;

use App\OrderDetail;
use App\Product;
use App\ProductCategory;
use App\Transaction;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['JWT','admin'], ['only' => ['index','store','destroy','update']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->page;
        $rows = $request->rowsPerPage;
        $skip = ($page - 1) * $rows;
        if ($request->descending == null || $request->descending == 'false') {
            $ascOrdesc = 'asc';
        } else {
            $ascOrdesc = 'desc';
        }
        $orderBy = ($request->sortBy) ? $request->sortBy : 'id';

        $products = DB::table('products')
        ->select('*')
        ->orderBy($orderBy, $ascOrdesc)
        ->skip($skip)
        ->take($rows)
        ->get();

        $total = DB::table('products')->count();

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
        $this->validate($request,[
            'name'=>'required',
            'product_category_id'=>'required',
            'price'=>'required|min:1|integer',
            'file'=>'sometimes|nullable|image',
        ]);
        
        
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



        try {
            $product = Product::create($request->except('file','select_location','quantity','payCashQuantity'));
        } catch (\Throwable $th) {
            return response($th,500);
        }

        if(!empty($request->select_location)){
            $location = explode(",",$request->select_location);
            $product->locations()->sync($location);
            $result = true;
            $payCashQuantity = json_decode($request->payCashQuantity,true);
            if(is_array($quantity = json_decode($request->quantity,true))){

                foreach ($quantity as $key => $value) {
                    $updated = $product->updateQuantity((int)$key,(int)$value,(int)$payCashQuantity[$key]);
                    if($updated != true){
                        $result = false;
                    }
                }
                if(!$result){
                    return response('數量更新錯誤',500);
                }
            }
        }
        

        return response($request,200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        
        $location = $product->getLocationAndQuantity();
        $product['location'] = $location;
        return response($product);
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
        if($request->hasFile('file')){
            $filename = $this->imageHandler($request->file('file'),$product->slug);
            if($filename){
                $request->merge(['img'=>$filename]);
            }else{
                return response('系統錯誤',500);
            }
        }

        

        try {
            $product->update($request->except('file','select_location','quantity','payCashQuantity'));
        } catch (\Throwable $th) {
            return response($th,500);
        }

        if(!empty($request->select_location)){
            $location = explode(",",$request->select_location);
            $product->locations()->sync($location);
        }else{
            $product->locations()->sync([]);
        }

        $result = true;
        $payCashQuantity = json_decode($request->payCashQuantity,true);
        if(is_array($quantity = json_decode($request->quantity,true))){

            foreach ($quantity as $key => $value) {
                $updated = $product->updateQuantity((int)$key,(int)$value,(int)$payCashQuantity[$key]);
                if($updated != true){
                    $result = false;
                }
            }
            if(!$result){
                return response('數量更新錯誤',500);
            }
        }

        return response('success',200);
        
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

    private function imageHandler($file,$product_slug){
        
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $path = "/images/products/" . $product_slug . "/";
        
        if(Storage::disk('local')->exists($path)){
            $result = Storage::deleteDirectory($path);
            if(!$result){
                return false;
            }
        }
        
        if(!Storage::disk('local')->put($path . $filename,File::get($file))){
            return false;//失敗:回傳false
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

        //檢查產品庫存 if 足夠 => 扣庫存
        if(!$product->minusOneQuantity($request->location_id)){
            return response('非常抱歉，此據點目前庫存已兌換完畢。',400);
        }

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

    //for users
    public function getAllProduct(){
        
        $products = Product::allAvailable();
        $cats = ProductCategory::all();

        return response([
            'products'=>$products,
            'cats'=>$cats,
        ],200);

    }


    public function list(Request $req){
        $products = Product::where('public',1)->orderBy('id','desc')->get();
        return view('product.list',[
            'products'=>$products,
            'token'=>$req->token,
        ]);
    }

    public function detail($slug){
        $product = Product::where('slug',$slug)->firstOrFail();
        return view('product.detail',[
            'product'=>$product,
        ]);
    }

}
