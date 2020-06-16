@extends('main')

@section('title','| 購物車')

@section('css')
<style>

</style>
@endsection

@section('content')

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <table class="table">
                <thead>
                    <tr>
                        {{-- <th scope="col"></th> --}}
                        <th scope="col">產品</th>
                        <th scope="col">價錢</th>
                        <th scope="col">數量</th>
                        <th scope="col">小計</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                    <tr>
                        {{-- <td style="width: 40px">
                            <div class="btn btn-sm btn-danger">刪除</div>
                        </td> --}}
                        <td style="width:120px">
                            <div>
                                <p>{{$p->name}}</p>
                            </div>
                            <?php $img = '/images/products/' . $p->slug . '/' . $p->img ?>
                            <div style="height: 80px;width:100%">
                                <img style="height:auto;width:auto;max-height: 100%;max-width:100%" src="{{$img}}">
                            </div>
                        </td>
                        <td>
                            <div>
                                <span style="line-height: 38px">樂幣:{{$p->price}}</span>
                            </div>
                            <div>
                                <span style="line-height: 38px">現金:{{$p->pay_cash_price}}樂幣:{{$p->pay_cash_point}}</span>
                            </div>
                            {{-- <div>
                                <span>樂幣</span>
                            </div> --}}
                        </td>
                        <td>
                            <div>
                                <input type="number" class="input-point-{{$p->id}} input-quantity form-control d-inline-block" 
                                style="width:80px" value="0" data-point="{{$p->price}}" 
                                data-product-id="{{$p->id}}" min="0">
                            </div>
                            <div>
                                <input type="number" class="input-cash-point-{{$p->id}} input-quantity form-control d-inline-block" 
                                style="width:80px" value="0" data-cash="{{$p->pay_cash_price}}" data-point="{{$p->pay_cash_point}}" 
                                data-product-id="{{$p->id}}" min="0">
                            </div>
                            {{-- <div>
                                <input type="number" class="form-control d-inline-block" style="width:120px">
                            </div> --}}
                        </td>
                        <td style="width:120px">
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


    <div class="row">
        <div class="col-12">
            <div class="btn btn-block btn-primary btn-lg">下一步</div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form>
                <div class="form-group">
                    <label for="exampleInputEmail1">收件人</label>
                    <input type="text"" class=" form-control" placeholder="收件人">
                </div>
                <div class="form-group">
                    <label for="exampleInputEmail1">聯絡電話</label>
                    <input type="text"" class=" form-control" placeholder="聯絡電話">
                </div>
                <div class="form-row">
                    <div class="col-12 mb-3">
                        <label for="validationTooltip04">地址</label>
                        <div class="form-control" id="twzipcode"></div>
                        <input type="text"" class=" form-control" placeholder="地址">
                    </div>
                    
                </div>
                <button type="submit" class="btn btn-block btn-lg btn-primary">確定送出</button>
            </form>
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
</script>
@endsection