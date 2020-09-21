@extends('main')

@section('title','| 訂單詳情')

@section('css')
<style>

</style>
@endsection

@section('content')

    @include('component.titlebar',[
        'url'=>'/order/list',
        'title'=>'訂單列表',
    ])

    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4>訂單詳情</h4>
                <h5>訂單號碼：{{$order_numero}}</h5>
                <hr>
                @if ($orderDelievery)
                <div>
                    <span>收件人:{{$orderDelievery->receiver_name}}</span>
                </div>
                <div>
                    <span>聯絡電話:{{$orderDelievery->receiver_phone}}</span>
                </div>
                <div>
                    <span>地址:{{$orderDelievery->county}}{{$orderDelievery->district}}{{$orderDelievery->address}}</span>
                </div>    
                @else

                @endif
                
                <hr>
                <div class="mb-4">
                    <table style="width:100%;text-align:center">
                        <tr>
                            <th style="text-align: left">產品</th>
                            <th>金額</th>
                            <th>狀態</th>
                        </tr>
                        @foreach ($orders as $order)
                        <tr>
                            <td colspan="3" style="text-align: left">
                                <h6 class="mt-2">{{$order->name}}</h6>
                            </td>
                        </tr>
                        <tr>
                            <td style="width:80px">
                                <img style="width:100%" src="{{$productImageDict[$order->product_id]}}">
                            </td>
                            <td>
                                <span>樂幣:{{$order->total_point}}</span><br>
                                <hr class="m-0">
                                <span>台幣:{{$order->total_cash}}</span><br>
                                <span>運費:{{$shipping_fee}}</span><br>
                                <span>總額:{{$order->total_cash + $shipping_fee}}</span>
                            </td>
                            <td>
                                <span>{{$shipStatusDict[$order->ship_status]}}</span>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection


@section('js')
@endsection