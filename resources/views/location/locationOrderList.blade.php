@extends('location.layout')

@section('title',"| 據點訂單")

@section('css')
    @include('location.pagination_style')
@endsection

@section('content')

    @include('location.titlebar',[
        'url'=>"/view_myLocation",
        'title'=>'據點列表',
    ])

    @include('location.pagination',[
        'totalPage'=>$pagination->totalPage,
        'page'=>$pagination->page,
        'url'=>"/view_locationOrderList/$slug",
    ])

<?php
    $colorDict = [
        '0'=>'lightgray',
        '1'=>'lightcoral',
        '2'=>'lightskyblue',
        '3'=>'cornflowerblue',
        '4'=>'forestgreen',
    ];                   
    $statusDict = [
        '0'=>'待出貨',
        '1'=>'準備中',
        '2'=>'已出貨',
        '3'=>'已到貨',
        '4'=>'結案',
    ];
?>
@if (empty($orderList))
    <h1 style="margin:40px 0;text-align:center">目前無資料</h1>
@endif
@foreach ($orderList as $order)
<div class="data-cell">
    <div>
        <span>{{$userDict[$order->user_id]}}</span>
        <span style="border-radius:.3rem;font-size:24px;padding: 4px 8px;background:{{$colorDict[$order->ship_status]}}">{{$statusDict[$order->ship_status]}}</span>
    </div>
    <div>
        <span>編號：{{$order->order_numero}}</span>
    </div>
    <div style="line-height: 20px">
        <span style="font-size: 20px">日期：{{$order->created_at}}</span>
    </div>
    <div style="margin-top: 8px">
        <a href="/view_locationOrderDetail/{{$slug}}/{{$order->order_numero}}" style="background:darkgray ">詳細</a>
    </div>
    
</div>
@endforeach

    @include('location.pagination',[
        'totalPage'=>$pagination->totalPage,
        'page'=>$pagination->page,
        'url'=>"/view_locationOrderList/$slug",
    ])

@endsection

@section('js')

@endsection