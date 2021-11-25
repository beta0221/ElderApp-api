@extends('location.layout')

@section('title',"| " . $location->name . "-" . $product->name)

@section('css')
    <style>
        .name-list-outter{
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background:#fff;
        }
        .name-list-outter .top-bar{
            height: 80px;
            background-color: lightgray;
            text-align: center;
            overflow: hidden;
        }
        .top-bar .title{
            line-height: 80px;
            font-size: 24px;
            color: teal;
            margin: 0 32px;
        }
        .name-list-outter .top-bar .top-bar-button{
            position:absolute;
            left:0;
            top: 0;
            width:120px;
            height: 80px;
            text-align: center;
            line-height: 80px;
            font-size: 24px;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
            z-index: 1;
        }
        .name-list-outter .top-bar .top-bar-right-button{
            position:absolute;
            right:0;
            top: 0;
            width:120px;
            height: 80px;
            text-align: center;
            line-height: 80px;
            font-size: 24px;
            cursor: pointer;
            color: #fff;
            text-decoration: none;
            z-index: 1;
        }
        .name-list{
            padding: 12px;
        }
        .name-list .name-cell{
            padding: 12px;
            font-size: 24px;
            border-radius: .3rem;
            background-color: teal;
            margin-bottom: 12px;
            color: #fff;
        }
        .check-btn{
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            padding:4px 12px;
            background-color: #fff;
            color: teal;
            border-radius: .3rem;
        }
        .isReceived{
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background-color: teal;
            color: #fff;
            border-radius: .3rem;
        }
        #no-order{
            font-size: 24px;
            color: gray;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
            background-color: #fff;
        }
    </style>
    @include('location.pagination_style')
@endsection

@section('content')
<div class="name-list-outter">
    <div class="top-bar">
        <a class="top-bar-button" href="{{route('productList',['location_slug'=>$location->slug])}}">＜Back</a>
        <div class="title">{{$product->name}}</div>
        <a class="top-bar-right-button" href="{{route('receiveList',['location_slug'=>$location->slug,'product_slug'=>$product->slug])}}">領取紀錄</a>
    </div>

    {{-- <div style="padding: 8px 12px 0 12px;">
        <input id="search-input" type="text" style="width:80%;height:40px;padding:0 4px" placeholder="姓名">
        <button onclick="search()" style="width:18%;height:40px">搜尋</button>
    </div> --}}
    <div style="padding: 0 12px;color:gray;margin-top:8px">Total：{{$pagination->total}}</div>

    

    <div class="name-list">

    @include('location.pagination',[
        'totalPage'=>$pagination->totalPage,
        'page'=>$pagination->page,
        'url'=>'/order-list/location/' . $location->slug . '/' . $product->slug,
    ])
    @if (count($orders) == 0)
        <div id='no-order' class='name-cell'>目前無人兌換</div>
    @else
        @foreach ($orders as $order)
        <div class='name-cell' data-name="{{$order->name}}">
            <span>{{$order->name}} (編號:{{$order->id_code}}) </span>
            <div order_id="{{$order->id}}" user_id="{{$order->user_id}}" product_id="{{$order->product_id}}" class='check-btn'>領取</div>
        </div>
        @endforeach
    @endif
    </div>
</div>
@endsection

@section('js')

    @include('location.jquery')
    
    <script>
    $(document).ready(function(){
        $.ajaxSetup({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });
    
        // $('#search-input').on('keyup touchend',function(){
        //     var search_value = $(this).val();
        //     $('.name-cell').each(function (index, item) { 
        //         var name = $(item).data('name');
        //          if(name.includes(search_value)){
        //             $(item).show();
        //          }else{
        //             $(item).hide();
        //          }
        //     });
        // });
    
        $(document).on('click','.check-btn',function(){
    
            let id = parseInt($(this).attr('order_id'));
            let user_id = parseInt($(this).attr('user_id'));
            let product_id = parseInt($(this).attr('product_id'));
    
            $.ajax({
                type: "POST",
                url: "/order-detail/receive",
                data: {
                'id':id,
                'user_id':user_id,
                'product_id':product_id,
                'location_id':{{$location->id}},  
                },
                dataType: "json",
                success: (res)=> {
                    $(this).addClass('isReceived');
                    $(this).removeClass('check-btn');
                    $(this).html('已領取');
                },
                error: function (error) {
                    alert('錯誤');
                }
            });
    
        });
    
    
    });

    function search(){
        let name = $('#search-input').val();
        let url = window.location.pathname + "?name=" + name;
        window.location.href = url;
    }
    </script>
@endsection