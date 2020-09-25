@extends('main')

@section('title','| '.$product->name)

@section('css')
<style>
.float-area{
    position: fixed;
    right: 0;
    bottom: 56px;
    height: 56px;
    width: 100%;
    display: flex;
}
.float-btn{
    flex: 1;
    height: 100%;
    color: #fff;
    text-align: center;
}
.float-btn span{
    font-size: 28px;
    line-height: 56px;
    /* position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%); */
}
</style>
@endsection

@section('content')

@include('component.titlebar',[
    'url'=>'/product/list',
    'title'=>'產品首頁',
])

<div class="container" style="margin-bottom: 56px">

    

    <div class="row">
        <div class="col-sm-12">
            <div>
                <?php $img = config('app.static_host') . "/products/$product->slug/$product->img"; ?>
                <img class="w-100" src="{{$img}}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <h2 class="mb-3">{{$product->name}}</h2>
            <h3>台幣+樂幣</h3>
            <h3><font color="#ff5252">{{$product->pay_cash_price}}</font>+<font color="#fb8c00">{{$product->pay_cash_point}}</font></h3>
            <h3>台幣</h3>
            <h3>
                <font color="#ff5252">{{$product->cash}}</font>
                <font style="text-decoration: line-through;" size="3" color="gray">原價:{{$product->original_cash}}</font>
            </h3>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <hr>
            <p>
                {!! nl2br($product->info)  !!}
            </p>
            
        </div>
    </div>
</div>

<div class="float-area">
    <div class="float-btn bg-danger">
        <a href="/cart" style="color:#fff;"><span>前往結帳</span></a>
    </div>
    <div class="float-btn bg-warning mb-2" onclick="add_to_cart()">
        <span>加入購物車</span>
    </div>
</div>

@endsection

@section('js')
<script>
    const product_id = {{$product->id}};

    function add_to_cart(){
        $.ajax({
            type: "POST",
            url: "/api/cart/store/"+product_id,
            dataType: "json",
            success: function (response) {
                window.happyAlert('成功加入購物車');
            }
        });
    }

</script>
@endsection