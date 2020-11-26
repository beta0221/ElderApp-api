@extends('location.layout')

@section('title',"| ")

@section('css')
    
@endsection

@section('content')

@include('location.titlebar',[
    'url'=>"/view_locationOrderList/$slug",
    'title'=>'訂單列表',
])


<h3 style="font-size: 28px">訂單編號：{{$orders[0]->order_numero}}</h3>
<hr>
<?php
    $final_cash = 0;
?>
@foreach ($orders as $order)
    
    <div style="margin-top: 12px;font-size:24px">
        <div>
            <img style="max-height: 100px;max-width:100px" src="{{$productImageDict[$order->product_id]}}">
        </div>
        <div>
            <span>商品：</span>{{$order->name}}
        </div>
        <div>
            <span>總數：</span>{{$order->total_quantity}}
        </div>
        <div>
            <span>總金額：</span>{{$order->total_cash}}
        </div>
    </div>
    
    <hr>
    
    
    <?php $final_cash += $order->total_cash; ?>
@endforeach
<div style="margin-top: 20px;margin-bottom:20px">
    <span style="font-size:32px;">訂單總金額：{{$final_cash}}</span>
</div>

<form action="/view_nextStatus/{{$slug}}/{{$orders[0]->order_numero}}" method="POST">
    @csrf
    <button style="width:100%;cursor:pointer;border-radius:.3rem;background:forestgreen;padding:8px 12px;font-size:32px;color:#fff" type="submit">結案</button>
</form>

@endsection

@section('js')
@include('location.jquery')
<script>
    
</script>
@endsection