<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$location->name}}-兌換清單</title>
    <style>
        *{
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            position: relative;
        }
        .outter{
            padding: 24px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            align-self: center;
            max-width: 600px;
            width: 100%;
            height: 100vh;
            border:.5px solid lightgray;
            overflow-y: scroll;
        }
        .product-list{
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background:#fff;
            overflow-y:scroll;
        }
        .product-btn{
            padding: 12px;
            border-radius: .3rem;
            background-color: green;
            margin-bottom: 12px;
            color: #fff;
            cursor: pointer;
        }
        .product-btn img{
            max-width: 80px;
            max-height:80px;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
        }
        .product-btn span{
            display: inline-block;
            line-height: 80px;
            vertical-align: top;
            height: 80px;
            min-width:80px;
            margin-right: 12px;
            font-size: 24px;
        }

    </style>
</head>
<body>
    <div class="outter">
        <div class="product-list">
            <?php $static_host = config('app.static_host'); ?>
            @foreach($products as $product)
            <div class="product-btn" onclick="location.href='{{route('orderList',['location_slug'=>$location->slug,'product_slug'=>$product->slug])}}'">
                <span>
                    <img src="{{$static_host}}/products/{{$product->slug}}/{{$product->img}}">
                </span>
                <span>
                    {{$product->name}}
                </span>
            </div>
            @endforeach
        </div>
        
    </div>
</body>

</html>