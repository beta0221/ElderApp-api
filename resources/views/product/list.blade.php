@extends('main')

@section('title','| 產品')

@section('css')
<style>
    .product-cell{
        display: inline-block;
        width: calc(50% - 3px);
    }
	.image-outter{
        padding-top: 100%;
    }
    .image-inner{
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    .image-inner a{
        display: block;
        width: 100%;
        height: 100%;
    }
    .image-inner img{
        top: 50%;
        left: 50%;
        transform: translate(-50%,-50%);
        max-height: 100%;
        max-width: 100%;
    }
    .product-info-box{
        background: rgba(230, 230, 230, 0.918);
        border-radius: .3rem;
    }
    .product-name-box{
        height: 56px;
    }
</style>
@endsection

@section('content')

<div>

    <div>
        @foreach($products as $product)
        <?php $img = config('app.static_host') . "/products/$product->slug/$product->img"; ?>
        <div class="product-cell p-2">
            
            <div class="image-outter">
                <div class="image-inner">
                    <a href="/product/detail/{{$product->slug}}">
                        <img src="{{$img}}">
                    </a>
                </div>
            </div>
            <div class="p-1 product-info-box">
                <div class="product-name-box">
                    <h4>{{$product->name}}</h4>
                </div>
                
                <h5>現金</h5>
                <h5><font color="#ff5252">{{$product->cash}}</font></h5>
                <h5>現金/樂幣</h5>
                <h5><font color="#ff5252">{{$product->pay_cash_price}}</font>/<font color="#fb8c00">{{$product->pay_cash_point}}</font></h5>
            </div>
            
            
        </div>
        @endforeach
    </div>

</div>

@endsection

@section('js')
<script>
    
</script>
@endsection