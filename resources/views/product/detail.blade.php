@extends('main')

@section('title','| '.$product->name)

@section('css')
<style>
.float-area{
    position: fixed;
    right: 0;
    bottom: 56px;
}
.float-btn{
    width: 56px;
    height: 56px;
    border-radius: 50%;
    color: #fff;
}
.float-btn span{
    font-size: 32px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%,-50%);
}
</style>
@endsection

@section('content')

<div class="container">
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

<div class="float-area p-2">
    <div class="float-btn bg-warning mb-2" onclick="add_to_cart()">
        <span class="material-icons">
            add_shopping_cart
        </span>
    </div>
    <div class="float-btn bg-danger">
        <span class="material-icons">
            shopping_basket
        </span>
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