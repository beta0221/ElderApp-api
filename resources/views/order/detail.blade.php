@extends('main')

@section('title','| 訂單詳情')

@section('css')
<style>

</style>
@endsection

@section('content')

    @if (isset($noFooter) && $noFooter == 1)
        @include('component.titlebar',[
            'url'=>'/order/list?noFooter=1',
            'title'=>'訂單列表',
        ])
    @else
        @include('component.titlebar',[
            'url'=>url()->previous(),
            'title'=>'訂單列表',
        ])
    @endif

    

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
                <hr>
                @endif
                
                
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
                                @if (!empty($locationDict) && $order->location_id)
                                    <span>領取：{{$locationDict[$order->location_id]->name}}</span><br>
                                    <span>{{$locationDict[$order->location_id]->address}}</span>
                                @endif
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
                                {{-- <span>總額:{{$order->total_cash}}</span> --}}
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