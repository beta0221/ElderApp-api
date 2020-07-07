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
</style>
@endsection

@section('content')

<div>

    <div>
        @foreach($products as $product)
        <?php $img = '/images/products/'.$product->slug.'/'.$product->img ?>
        <div class="product-cell p-2">
            
            <div class="image-outter">
                <div class="image-inner">
                    <a href="/product/detail/{{$product->slug}}">
                        <img src="{{$img}}">
                    </a>
                </div>
            </div>
            <div class="pt-2 pb-2">
                <h4>{{$product->name}}</h4>
                <h4>樂幣:{{$product->price}}</h4>
                <h4>售價:{{$product->pay_cash_price}}</h4>
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