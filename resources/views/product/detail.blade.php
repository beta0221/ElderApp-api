@extends('main')

@section('title','| '.$product->name)

@section('css')
<style>
    .title-bar{
        height: 56px;
        background:rgba(0,0,0,0.05);
        padding: 0 12px;
    }
    .title-bar span,.title-bar a{
        color: #212529;
        vertical-align: top;
        display: inline-block;
        line-height: 56px;
    }
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

<div class="title-bar mb-2">
    <a href="/product/list">
        <span class="material-icons">arrow_back</span>
        <span>產品首頁</span>
    </a>
    
</div>

<div class="container" style="margin-bottom: 56px">

    

    <div class="row">
        <div class="col-sm-12">
            <div>
                <?php $img = '/images/products/'.$product->slug.'/'.$product->img ?>
                <img class="w-100" src="{{$img}}">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <h2>{{$product->name}}</h2>
            <h2>樂幣:{{$product->price}}</h2>
            <h2>售價:{{$product->pay_cash_price}}</h2>
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
        {{-- <span class="material-icons">
            shopping_basket
        </span> --}}
        <span>前往結帳</span>
    </div>
    <div class="float-btn bg-warning mb-2" onclick="add_to_cart()">
        {{-- <span class="material-icons">
            add_shopping_cart
        </span> --}}
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