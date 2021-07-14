@extends('main')

@section('title','| 我的訂單')

@section('css')
<style>
    
</style>
@endsection

@section('content')


    @if (isset($noFooter) && $noFooter == 1)
            {{-- no footer --}}
    @else
        @include('component.titlebar',[
            'url'=>'/product/list',
            'title'=>'產品首頁',
        ])
    @endif
    

    <div class="container">

        <div class="row align-items-center mt-2">
            <div class="col-sm-12">
                <h4>我的訂單</h4>
            </div>    
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table style="width: 100%">
                    {{-- <tr>
                        <th>日期</th>
                        <th>訂單編號</th>
                        <th>產品</th>
                        <th></th>
                    </tr> --}}
                    
                    @if(count($orderList) == 0)
                    <tr style="height: 160px">
                        <td class="mt-4 mb-4" colspan="4" style="text-align: center">
                            <h4>無訂單記錄</h4>
                        </td>
                    </tr>
                    @endif

                    @foreach ($orderList as $order)
                    <tr>
                        <td class="w-100">
                            <div>
                                <h5 class="m-0">編號：{{$order->order_numero}}</h5>
                            </div>
                            <div class="mb-4">
                                <table class="w-100">
                                    @foreach ($order->list as $index => $o)
                                    <tr>
                                        <td style="width: 80px">
                                            <img class="w-100" src="{{$productImageDict[$o->product_id]}}">
                                        </td>
                                        <td>{{$o->name}}</td>
                                        @if ($index == 0)
                                        <td style="width: 56px" rowspan="{{count($order->list)}}">
                                            <?php 
                                                $href = "/order/detail/".$order->order_numero;

                                                if (isset($noFooter) && $noFooter == 1){
                                                    $href .= "?noFooter=1";
                                                }
                                            ?>
                                            <a href="{{$href}}"><div class="btn btn-primary btn-sm">詳情</div></a>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <hr>
                        </td>
                    </tr>
                    @endforeach
                </table> 
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="mt-4 mb-4">
                    @include('component.pagination',[
                        'totalPage'=>$totalPage,
                        'page'=>$page,
                        'url'=>'/order/list',
                    ])
                </div>
            </div>
        </div>


    </div>
@endsection


@section('js')
@endsection