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
                <div class="mt-4">
                    <h3>訂單號碼：</h3>
                    <h3>{{$order_numero}}</h3>
                </div>
                <div class="mb-2">
                    <a href="/order/detail/{{$order_numero}}"><div class="btn btn-success btn-lg">訂單內容</div></a>
                </div>
                <div>
                    <a href="/product/list"><div class="btn btn-primary btn-lg">繼續購物</div></a>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('js')
@endsection