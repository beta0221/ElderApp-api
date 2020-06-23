@extends('main')

@section('title','| 購買成功')

@section('css')
<style>

</style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h1>謝謝！</h1>
                <h2>訂單號碼：{{$order_numero}}</h2>
            </div>
        </div>
    </div>
@endsection


@section('js')
@endsection