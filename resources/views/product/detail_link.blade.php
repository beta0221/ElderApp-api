@extends('universal')

@section('title','| '.$product->name)

@section('css')

@endsection

@section('content')
<div class="container">
    
    <div class="row">
        <div class="col-sm-12">
            <?php $img = config('app.static_host') . "/products/$product->slug/$product->img"; ?>    
            <img class="w-100" src="{{$img}}">
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
@endsection

@section('js')

@endsection