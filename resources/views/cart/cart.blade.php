@extends('main')

@section('title','| 購物車')

@section('css')
<style>
    .step-2-section{
        display: none;
    }
    .delivery-section{
        display:none;
    }
    .location-section{
        display:none;
    }
    .table td{
        padding: 8px 0;
        text-align: center;
    }
    .table th{
        text-align: center;
    }
    .form-check-input{
        transform: scale(2.5);
    }
    .location-group{
        background:rgba(0,0,0,0.1);
        border-radius: .4rem;
    }
</style>
@endsection

@section('content')

@include('component.titlebar',[
    'url'=>'/product/list',
    'title'=>'產品首頁',
])

<div class="container mb-3 mt-3">

    <div class="row">
        <div class="col-sm-12">
            @if(Session::has('message'))
                <div class="alert alert-danger" role="alert">
                    {{Session::get('message')}}
                </div>
            @endif
        </div>
    </div>


    <div class="row">
        <div class="col-sm-12">
            <table style="width:100%;text-align: center">
                <tbody>
                    @if(count($products) == 0)
                    <tr>
                        <td colspan="4">
                            <h3 class="mt-4 mb-2">購物車中沒有商品</h3>
                        </td>
                    </tr>
                    @endif

                    @foreach($products as $p)
                    <?php $img = $static_host . "/products/$p->slug/$p->img"; ?>
                    <tr>
                        <td style="width:100%">
                            <div>
                                <div>
                                    <table style="width:100%">
                                        <tr>
                                            <td style="width:80px">
                                                <img style="height:auto;width:auto;max-height: 100%;max-width:100%" src="{{$img}}">
                                            </td>
                                            <td>
                                                {{$p->name}}
                                            </td>
                                            <td style="width:64px">
                                                <div class="btn btn-sm btn-danger" onclick="delete_product({{$p->id}})">刪除</div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div>
                                    <table style="width:100%">
                                        <tr>
                                            <td style="width:80px">
                                                <span style="line-height: 24px">台幣+樂幣</span><br>
                                                <span style="line-height: 24px"><font color="#ff5252">{{$p->pay_cash_price}}</font>+<font color="#fb8c00">{{$p->pay_cash_point}}</font></span><br>
                                                {{-- <span style="line-height: 24px">台幣</span><br>
                                                <span style="line-height: 24px"><font color="#ff5252">{{$p->cash}}</font></span> --}}
                                            </td>
                                            <td>
                                                <div>
                                                    <div style="line-height: 32px" class="btn btn-sm btn-secondary input-decrease quantity-btn" data-product-id="{{$p->id}}">◀</div>

                                                    <input type="number" class="input-cash-point-{{$p->id}} input-quantity form-control d-inline-block" 
                                                    style="width:48px;line-height:32px" value="1" data-cash="{{$p->pay_cash_price}}" data-point="{{$p->pay_cash_point}}" 
                                                    data-product-id="{{$p->id}}" min="1">

                                                    <div style="line-height: 32px" class="btn btn-sm btn-secondary input-increase quantity-btn" data-product-id="{{$p->id}}">▶</div>
                                                </div>
                                                {{-- <div class="mb-1">
                                                    <div style="line-height: 32px" class="btn btn-sm btn-secondary input-decrease" data-product-id="{{$p->id}}">◀</div>
                                                    <input type="number" class="input-cash-{{$p->id}} input-quantity form-control d-inline-block" 
                                                    style="width:48px;line-height: 32px" value="0" data-cash="{{$p->cash}}" 
                                                    data-product-id="{{$p->id}}" min="0">
                                                    <div style="line-height: 32px" class="btn btn-sm btn-secondary input-increase" data-product-id="{{$p->id}}">▶</div>
                                                </div> --}}
                                            </td>
                                            <td style="width:64px">
                                                <span style="line-height: 24px">台幣</span><br>
                                                <span id="subtotal-cash-{{$p->id}}" class="subtotal-cash" style="color:#ff5252">{{$p->pay_cash_price}}</span><br>
                                                <span style="line-height: 24px">樂幣</span><br>
                                                <span id="subtotal-point-{{$p->id}}" class="subtotal-point" style="color:#fb8c00">{{$p->pay_cash_point}}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <hr>

    <div class="delivery-type-section pl-3">
        <div class="form-check mb-3">
            <input class="form-check-input radio_delivery_type" type="radio" name="delivery_type" id="delivery_type_0" value="0" checked>
            <label class="form-check-label ml-2" for="delivery_type_0">據點取貨(免運費)</label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input radio_delivery_type" type="radio" name="delivery_type" id="delivery_type_1" value="1">
            <label class="form-check-label ml-2" for="delivery_type_1">宅配到我家</label>
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

    <div class="row mb-2 step-2-section">
        <div class="col-12">
            <div class="btn btn-block btn-secondary btn-lg mb-4" onclick="toStepOne()">上一步</div>
        </div>
        
        <div class="col-8 offset-4">
            <h5 style="display: none" id="shipping-fee">運費：<span style="color:#ff5252">{{$shipping_fee}}</span></h5>
            <h5 class="m-0">總計</h5>
            <hr class="mt-1 mb-1">
            
            <h5>台幣：<span id="total-cash" style="color:#ff5252">0</span></h5>
            <h5>樂幣：<span id="total-point" style="color:#fb8c00">0</span></h5>
            <h6 class="mt-4 text-secondary">剩餘樂幣：{{$wallet_remain}}</h6>
        </div>
    </div>
    

    <div class="row delivery-section">
        <div class="col-12">

            <form id="checkout-form" method="POST" action="/cart/checkOut">
                {{ csrf_field() }}

                <input id="delivery_type" type="hidden" name="delivery_type" value="">
                <input type="hidden" name="quantityDict" value="">
                <input type="hidden" name="locationDict" value="">

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


    <div class="row location-section mt-4">
        <div class="col-12">
            <table style="width:100%" class="mb-3">
                @foreach($products as $p)
                <?php $img = $static_host . "/products/$p->slug/$p->img"; ?>
                <tr>
                    <td style="width:80px">
                        <div>
                            <img style="height:auto;width:auto;max-height: 100%;max-width:100%" src="{{$img}}">
                        </div>
                    </td>
                    <td>
                        <div class="location-group pl-3 pt-3 pb-3">
                            @foreach($locationDict[$p->id] as $location)
                            <div class="form-check mb-3">
                                <input id="location_{{$location->id}}" class="form-check-input radio_location" type="radio" data-product-id="{{$p->id}}" name="location_{{$p->id}}" value="{{$location->id}}">
                                <label for="location_{{$location->id}}" class="form-check-label ml-2">{{$location->name}}</label>
                            </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endforeach
            </table>
            <div onclick="checkOutRequest()" class="btn btn-block btn-lg btn-primary">確定送出</div>
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

    const shipping_fee = {{$shipping_fee}};

    $('#twzipcode').twzipcode({
        "css": ["form-control","form-control","form-control"],
    });
    $(document).ready(function(){
        $('.input-quantity').on('change',function(){
            let id = $(this).data('product-id');
            cacu_product(id);
        });

        $('.input-decrease').on('click',function(){
            var input_dom = $(this).next();
            var input_dom_value = parseInt(input_dom.val());
            var product_id = $(this).data('product-id');
            if(input_dom_value == 0){return false;}
            input_dom_value -= 1;
            input_dom.val(input_dom_value);
            cacu_product(product_id);
        })

        $('.input-increase').on('click',function(){
            var input_dom = $(this).prev();
            var input_dom_value = parseInt(input_dom.val());
            var product_id = $(this).data('product-id');
            input_dom_value += 1;
            input_dom.val(input_dom_value);
            cacu_product(product_id);
        })

    });

    function cacu_product(id){
        let point = 0;
        let cash = 0;
        // let input_cash = $('.input-cash-'+id);
        // cash += (input_cash.val() * input_cash.data('cash'));
        let input_cash_point = $('.input-cash-point-'+id);
        point += (input_cash_point.val() * input_cash_point.data('point'));
        cash += (input_cash_point.val() * input_cash_point.data('cash'));
        
        $('#subtotal-point-'+id).html(point);
        $('#subtotal-cash-'+id).html(cash);
        
    }

    function getQuantityDict(){
        let quantityDict = {};
        $('.input-quantity').each(function(index,item){
            let product_id = $(this).data('product-id');
            let quantity = $(this).val();
            if(!quantityDict[product_id]){
                quantityDict[product_id] = {};
            }
            if($(this).data('point') != null){
                quantityDict[product_id]['point_cash'] = quantity;
            }else{
                quantityDict[product_id]['cash'] = quantity;
            }
        });
        console.log(quantityDict);
        return quantityDict;
    }

    function getLocationDict(){
        let locationDict = {};
        $('.radio_location').each(function(index,item){
            let product_id = $(this).data('product-id');
            let location_id = $(this).val();
            if($(this).prop('checked')){
                locationDict[product_id] = location_id;
            }
        });
        console.log(locationDict);
        return locationDict;
    }

    function checkOutRequest(){
        let quantityDict = getQuantityDict();
        let locationDict = getLocationDict();
        $('#delivery_type').val($('.radio_delivery_type:checked').val());
        $("[name='quantityDict']").val(JSON.stringify(quantityDict));
        $("[name='locationDict']").val(JSON.stringify(locationDict));
        $('#checkout-form').submit();
    }

    function delete_product(id){
        $.ajax({
            type: "POST",
            url: "/api/cart/destroy/"+id,
            dataType: "json",
            success: function (response) {
                if(response.s == 1){
                    location.href = "/cart";
                }else{
                    alert(response.m);
                }
            }
        });
    }

    function cacu_total(){
        var total_point = 0;
        var total_cash = 0;
        $('.subtotal-point').each(function(index,item){
            total_point += parseInt($(this).html());
        });
        $('.subtotal-cash').each(function(index,item){
            total_cash += parseInt($(this).html());
        });

        if($('.radio_delivery_type:checked').val() == 1){
            total_cash += shipping_fee;
            $('#shipping-fee').show();
        }else{
            $('#shipping-fee').hide();
        }

        $('#total-point').html(total_point);
        $('#total-cash').html(total_cash);
    }

    function toStepTwo(){
        if($('.radio_delivery_type:checked').val() == 1){
            $('.delivery-section').show();
        }else{
            $('.location-section').show();
        }
        cacu_total();
        $('.quantity-btn').hide();
        $('.step-1-section').hide();
        $('.step-2-section').show();
        $('.input-quantity').attr('disabled',true);
        $('.radio_delivery_type').prop('disabled',true);
    }

    function toStepOne(){
        $('.quantity-btn').show();
        $('.step-1-section').show();
        $('.step-2-section').hide();
        $('.delivery-section').hide();
        $('.location-section').hide();
        $('.input-quantity').attr('disabled',false);
        $('.radio_delivery_type').prop('disabled',false);
    }

</script>
@endsection