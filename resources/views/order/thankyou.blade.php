@extends('main')

@section('title','| 購買成功')

@section('css')
<style>

</style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-12 mt-4" style="text-align: center">
                <h3>非常感謝您的購買！</h3>
                <h5>此筆交易總共成立:</h5>
                <h3>{{count($order_numero_array)}}張訂單</h3>
                <div>
                    <a href="/product/list"><div class="btn btn-primary btn-lg">繼續購物</div></a>
                </div>
            </div>
        </div>
        
        @foreach ($order_numero_array as $i => $order_numero)
        <div class="row">
            <div class="col-sm-12 mt-2" style="text-align: center">
                
                <div class="mt-4">
                    <h3>{{$i+1}}.訂單號碼：</h3>
                    <h3>{{$order_numero}}</h3>
                </div>
                <div class="mb-2">
                    <a href="/order/detail/{{$order_numero}}"><div class="btn btn-success btn-lg">訂單內容</div></a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endsection


@section('js')
@endsection