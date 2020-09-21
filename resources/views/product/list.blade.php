@extends('main')

@section('title','| 產品')

@section('css')
<style>
    .product-cell{
        display: inline-block;
        width: 100%;
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
        overflow: hidden;
    }
    .page-item.active .page-link{
        background-color: #fb8c00;
        border-color: #fb8c00;
    }
    .page-link {
        color :#fb8c00;
    }
    .page-link:hover{
        color :#fb8c00;
    }
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
                    <h5>{{$product->name}}</h5>
                </div>
                <h5>台幣+樂幣</h5>
                <h5><font color="#ff5252">{{$product->pay_cash_price}}</font>+<font color="#fb8c00">{{$product->pay_cash_point}}</font></h5>
                <h5>台幣</h5>
                <h5>
                    <font color="#ff5252">{{$product->cash}}</font>
                    <font style="text-decoration: line-through;" size="2" color="gray">原價:{{$product->original_cash}}</font>
                </h5>
                
            </div>
            
            
        </div>
        @endforeach
    </div>

    <div class="mt-4 mb-4">
        @include('component.pagination',[
            'totalPage'=>$totalPage,
            'page'=>$page,
            'url'=>'/product/list',
        ])
    </div>

</div>

@endsection

@section('js')
<script>
    
</script>
@endsection