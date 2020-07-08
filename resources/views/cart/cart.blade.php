@extends('main')

@section('title','| 購物車')

@section('css')
<style>
    .step-2-section{
        display: none;
    }
    .table td{
        padding: 8px 0;
        text-align: center;
    }
    .table th{
        text-align: center;
    }
</style>
@endsection

@section('content')

<div class="container mb-3 mt-3">
    <div class="row">
        <div class="col-sm-12">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">產品</th>
                        <th scope="col">價錢</th>
                        <th scope="col">數量</th>
                        <th scope="col">小計</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($products) == 0)
                    <tr>
                        <td colspan="4">
                            <h3 class="mt-4 mb-2">購物車中沒有商品</h3>
                        </td>
                    </tr>
                    @endif

                    @foreach($products as $p)
                    <tr>
                        <td style="width:100px">
                            <div>
                                <p>{{$p->name}}</p>
                            </div>
                            <?php $img = '/images/products/' . $p->slug . '/' . $p->img ?>
                            <div style="height: 80px;width:100%">
                                <img style="height:auto;width:auto;max-height: 100%;max-width:100%" src="{{$img}}">
                            </div>
                            <div>
                                <div class="btn btn-sm btn-danger" onclick="delete_product({{$p->id}})">刪除</div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span style="line-height: 38px">樂幣:{{$p->price}}</span>
                            </div>
                            <div>
                                <span style="line-height: 38px">現金:{{$p->pay_cash_price}}樂幣:{{$p->pay_cash_point}}</span>
                            </div>
                        </td>
                        <td style="width: 56px;">
                            <div>
                                <input type="number" class="input-point-{{$p->id}} input-quantity form-control d-inline-block" 
                                style="width:56px" value="0" data-point="{{$p->price}}" 
                                data-product-id="{{$p->id}}" min="0">
                            </div>
                            <div>
                                <input type="number" class="input-cash-point-{{$p->id}} input-quantity form-control d-inline-block" 
                                style="width:56px" value="0" data-cash="{{$p->pay_cash_price}}" data-point="{{$p->pay_cash_point}}" 
                                data-product-id="{{$p->id}}" min="0">
                            </div>
                        </td>
                        <td style="width:100px">
                            <div style="line-height: 38px">樂幣:<span id="subtotal-point-{{$p->id}}" class="subtotal-point">0</span></div>
                            <div style="line-height: 38px">現金:<span id="subtotal-cash-{{$p->id}}" class="subtotal-cash">0</span></div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    <hr>
    <div class="row mb-2">
        <div class="col-6 offset-6">
            <h5>總計</h5>
            <hr>
            <h5>樂幣：<span id="total-point">0</span></h5>
            <h5>現金：<span id="total-cash">0</span></h5>
        </div>
    </div>

    @if(count($products) == 0)
    <div class="row step-1-section">
        <div class="col-12">
            <div class="btn btn-block btn-info btn-lg"><a class="text-white" href="/product/list">繼續逛逛</a></div>
        </div>
    </div>
    @else
    <div class="row step-1-section">
        <div class="col-12">
            <div class="btn btn-block btn-primary btn-lg" onclick="toStepTwo()">下一步</div>
        </div>
    </div>
    @endif

    <div class="row step-2-section">
        <div class="col-12">

            <div class="btn btn-block btn-light btn-lg" onclick="toStepOne()">上一步</div>

            <form id="checkout-form" method="POST" action="/cart/checkOut">
                {{ csrf_field() }}
                <input type="hidden" name="quantityDict" value="">

                <div class="form-group">
                    <label for="exampleInputEmail1">收件人</label>
                    <input type="text" name="receiver_name" class=" form-control" placeholder="收件人">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">聯絡電話</label>
                    <input type="text" name="receiver_phone" class=" form-control" placeholder="聯絡電話">
                </div>
                <div class="form-row">
                    <div class="col-12 mb-3">
                        <label for="validationTooltip04">地址</label>
                        <div class="form-control" id="twzipcode"></div>
                        <input type="text" name="address" class=" form-control" placeholder="地址">
                    </div>
                    
                </div>
                <div onclick="checkOutRequest()" class="btn btn-block btn-lg btn-primary">確定送出</div>
            </form>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

</div>


@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/jquery-twzipcode@1.7.14/jquery.twzipcode.min.js"></script>
<script>
    $('#twzipcode').twzipcode({
        "css": ["form-control","form-control","form-control"],
    });
    $(document).ready(function(){
        $('.input-quantity').on('change',function(){
            let id = $(this).data('product-id');
            cacu_product(id);
        });
    });

    function cacu_product(id){
        let point = 0;
        let cash = 0;
        let input_point = $('.input-point-'+id);
        point += (input_point.val() * input_point.data('point'));
        let input_cash_point = $('.input-cash-point-'+id);
        point += (input_cash_point.val() * input_cash_point.data('point'));
        cash += (input_cash_point.val() * input_cash_point.data('cash'));
        
        $('#subtotal-point-'+id).html(point);
        $('#subtotal-cash-'+id).html(cash);
        
        var total_point = 0;
        var total_cash = 0;
        $('.subtotal-point').each(function(index,item){
            total_point += parseInt($(this).html());
        });
        $('.subtotal-cash').each(function(index,item){
            total_cash += parseInt($(this).html());
        });
        $('#total-point').html(total_point);
        $('#total-cash').html(total_cash);
    }

    function checkOutRequest(){
        
        var quantityDict = {};
        $('.input-quantity').each(function(index,item){
            let product_id = $(this).data('product-id');
            let quantity = $(this).val();
            if(!quantityDict[product_id]){
                quantityDict[product_id] = {};
            }
            if($(this).data('cash') != null){
                quantityDict[product_id]['point_cash'] = quantity;
            }else{
                quantityDict[product_id]['point'] = quantity;
            }
        });
        console.log(quantityDict);
        $("[name='quantityDict'").val(JSON.stringify(quantityDict));
        $('#checkout-form').submit();
        // return;
        // $.ajax({
        //     type: "POST",
        //     url: "/api/cart/checkOut",
        //     headers:{
        //         'Authorization': 'Bearer '+getCookie('token'),
        //     },
        //     data: {
        //         'quantityDict':JSON.stringify(quantityDict),
        //         'receiver_name':$("[name='receiver_name'").val(),
        //         'receiver_phone':$("[name='receiver_phone'").val(),
        //         'county':$("[name='county'").val(),
        //         'district':$("[name='district'").val(),
        //         'zipcode':$("[name='zipcode'").val(),
        //         'address':$("[name='address'").val(),
        //     },
        //     dataType: "json",
        //     success: function (response) {
        //         if(response.s == 1){
        //             location.href = '/order/thankyou/'+response.order_numero
        //         }else{
        //             alert(response.m);
        //         }
        //     }
        // });
    }

    function delete_product(id){
        $.ajax({
            type: "POST",
            url: "/api/cart/destroy/"+id,
            dataType: "json",
            success: function (response) {
                if(response.s == 1){
                    location.reload();
                }else{
                    alert(response.m);
                }
            }
        });
    }

    function toStepTwo(){
        $('.step-1-section').hide();
        $('.step-2-section').show();
        $('.input-quantity').attr('disabled',true);
    }

    function toStepOne(){
        $('.step-1-section').show();
        $('.step-2-section').hide();
        $('.input-quantity').attr('disabled',false);
    }

</script>
@endsection