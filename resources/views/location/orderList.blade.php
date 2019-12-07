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
        .name-list-outter{
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background:#fff;
            display: none;
        }
        .name-list-outter .top-bar{
            height: 80px;
            background-color: lightgray;
            text-align: center;
        }
        .top-bar .title{
            line-height: 80px;
            font-size: 32px;
            color: teal;
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
</head>
<body>
    <div class="outter">
        <div class="product-list">
            @foreach($products as $product)
            <div class="product-btn" onclick="selectProduct({{$product->id}},'{{$product->name}}')">
                <span>
                    <img src="/images/products/{{$product->slug}}/{{$product->img}}">
                </span>
                <span>
                    {{$product->name}}
                </span>
            </div>
            @endforeach
        </div>
        <div class="name-list-outter">
            <div class="top-bar">
                <div class="top-bar-button" onclick="goBack()">
                    ＜Back
                </div>
                <div class="title"></div>
            </div>
            <div class="name-list">

            </div>
        </div>
    </div>
</body>
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
  <script>
      $(document).ready(function(){

        $.ajaxSetup({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                
            }
        })

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

      })
      

      function selectProduct(id,name){
        $('.top-bar .title').html(name);
        $('.name-cell').remove();
        $('.name-list-outter').show();
        $.ajax({
            type: "GET",
            url: "/api/order-list/location/{{$location->id}}/"+id,
            dataType: "json",
            success: function (res) {
                if(res.length <= 0){
                    $('.name-list').append("<div id='no-order' class='name-cell'>目前無人兌換</div>");
                }else{
                    res.forEach(o => {
                        $('.name-list').append(`<div class='name-cell'><span>${o.name} (編號:${o.id_code}) </span><div order_id="${o.id}" user_id="${o.user_id}" product_id="${o.product_id}" class='check-btn'>領取</div></div>`);
                    });
                }
            },
            error:function(error){
                console.log(error);
            }
        });
      }

      function goBack(){
          $('.name-list-outter').hide();
      }
  </script>
</html>