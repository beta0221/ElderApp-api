@extends('main')

@section('title','| 訂單詳情')

@section('css')
<style>

</style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h3>訂單詳情</h3>
                <h5>訂單號碼：{{$order_numero}}</h5>
                <hr>
                <div>
                    <span>收件人:{{$orderDelievery->receiver_name}}</span>
                </div>
                <div>
                    <span>聯絡電話:{{$orderDelievery->receiver_phone}}</span>
                </div>
                <div>
                    <span>地址:{{$orderDelievery->county}}{{$orderDelievery->district}}{{$orderDelievery->address}}</span>
                </div>
                <hr>
                <div>
                    <table style="width:100%;text-align:center">
                        <tr>
                            <th>產品</th>
                            <th>金額</th>
                            <th>狀態</th>
                        </tr>
                        @foreach ($orders as $order)
                        <tr>
                            <td style="width:80px">
                                <span>{{$order->name}}</span><br>
                                <img style="width:100%" src="{{$productImageDict[$order->product_id]}}">
                            </td>
                            <td>
                                <span>樂幣:{{$order->total_point}}</span><br>
                                <span>現金:{{$order->total_cash}}</span>
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