@extends('main')

@section('title','| 我的訂單')

@section('css')
<style>

</style>
@endsection

@section('content')
    <div class="container">

        <div class="row align-items-center mt-2">
            <div class="col-sm-12">
                <h3>我的訂單</h3>
                
            </div>    
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table>
                    <tr>
                        <th>日期</th>
                        <th>訂單編號</th>
                        <th>產品</th>
                        <th></th>
                    </tr>
                    @foreach ($orderList as $order)
                    <tr>
                        <td>{{$order->created_at}}</td>
                        <td>{{$order->order_numero}}</td>
                        <td>
                            <table>
                                @foreach ($order->list as $o)
                                <tr>
                                    <td>
                                        <span>{{$o->name}}</span><br>
                                        <img class="w-100" src="{{$productImageDict[$o->product_id]}}">
                                    </td>
                                </tr>        
                                @endforeach
                            </table>
                        </td>
                        <td>
                            <a href="/order/detail/{{$order->order_numero}}"><div class="btn btn-primary btn-sm">詳情</div></a>
                        </td>
                    </tr>
                    @endforeach
                </table> 
            </div>
        </div>


    </div>
@endsection


@section('js')
@endsection