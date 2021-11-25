<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>兌換清單</title>

    <style>
        body{
            text-align: center!important;
        }
        .receive-btn{
            background-color: green;
            color: #fff;
            padding: 8px 0;
            font-size: 20px;
            cursor: pointer;
        }
        .isReceived{
            background-color: orange;
            color: #fff;
            padding: 8px 0;
            font-size: 20px;
        }
    </style>
</head>
<body>
    
    <h2>兌換清單：{{$user->name}}</h2>
    <hr>

    @if (count($orderList) == 0)
        <h2>目前無兌換商品。</h2>
    @endif

    @foreach ($orderList as $order)
    <?php $order = (object)$order; ?>

        <div style="margin-bottom: 3rem;padding:0 3rem">
            <h3>{{$order->name}}</h3>
            <h5>{{$order->location_name}}</h5>
            <div>
                <img src="{{$order->imgUrl}}" style="max-width:100%">
            </div>
            
            <div class="receive-btn" data-id="{{$order->id}}" data-user-id="{{$order->user_id}}" data-product-id="{{$order->product_id}}" data-location-id="{{$order->location_id}}">
                領取
            </div>
        </div>
        
    @endforeach


    @include('location.jquery')

    <script>
        $(document).ready(function(){
            $.ajaxSetup({
                headers:{
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                }
            });
        });

        $(document).on('click','.receive-btn',function(){
    
            let id = parseInt($(this).data('id'));
            let user_id = parseInt($(this).data('user-id'));
            let product_id = parseInt($(this).data('product-id'));
            let location_id = parseInt($(this).data('location-id'));

            $.ajax({
                type: "POST",
                url: "/order-detail/receive",
                data: {
                'id':id,
                'user_id':user_id,
                'product_id':product_id,
                'location_id':location_id,  
                },
                dataType: "json",
                success: (res)=> {
                    $(this).addClass('isReceived');
                    $(this).removeClass('receive-btn');
                    $(this).html('已領取');
                },
                error: function (error) {
                    alert('錯誤');
                }
            });

        });
    </script>
</body>
</html>