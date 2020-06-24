@extends('main')

@section('title','| 我的訂單')

@section('css')
<style>

</style>
@endsection

@section('content')
    <div class="container">
        <div class="align-items-center">
            <div class="col-sm-12">
                <h1>我的訂單</h1>
                <hr>
            </div>    
         
            <table class="table">
                <thead>
                    <tr>
                        <th>日期</th>
                        
                        <th>訂單編號</th>
                        <th>產品</th>
                        <th>價格</th>
                        <th>數量</th>
                        <th>紅利折扣</th>
                        <th>總價</th>
                        <th>付款方式</th>
                        <th>付款狀態</th>
                        <th>出貨狀態</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                    {{$order->name}}              
                
                    <tr>
                    <td>{{$order->created_at}}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                    </tr>
                    @endforeach
                </tbody>
            </table> 
            
            
           
        </div>
    </div>
@endsection


@section('js')
@endsection