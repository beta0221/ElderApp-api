@extends('universal')

@section('title','| ' . $post->title)

@section('css')
<style>
    .post-title-row{
        height: 80px;
        width:80px;
        font-size: 20px;
        line-height:80px;
    }
    .post-comment-row{
        height:40px;
        width:40px;
        font-size: 16px;
        line-height:40px;
    }
    .image-outter{
        overflow: hidden;
        border-radius: 50%;
        vertical-align: middle;
        display:inline-block
    }
    .user-name{
        vertical-align:middle;
    }
    .comment-cell{
        border-radius: 8px;
        border:1px solid gray;
    }
</style>
@endsection

@section('content')
<div class="container">
    
    <div class="row">
        <div class="col-sm-12">
            <img class="w-100" src="{{$post->post_image}}">
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 mb-2">
            <div class="image-outter post-title-row mr-2">
                <img src="{{$post->user_image}}">
            </div>
            <span class="user-name post-title-row" >{{$post->user_name}}</span>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <h3>{{$post->title}}</h3>
        </div>
    </div>
    
    <div class="row">
        <div class="col-sm-12">
            <hr>
            <p style="font-size: 20px">
                {!! nl2br($post->body)  !!}
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <hr>

            @foreach ($commentList as $comment)
                <?php $comment = (object)$comment; ?>
                <div class="comment-cell pt-2 pl-4 pr-4 pb-2 mb-2">
                    <div class="image-outter post-comment-row mr-2">
                        <img src="{{$comment->user_image}}">
                    </div>
                    <span class="user-name post-comment-row" >{{$comment->user_name}}</span>
                    <div class="mt-2">
                        {{$comment->body}}
                    </div>
                </div>
                
            @endforeach

        </div>
    </div>

</div>
@endsection

@section('js')

@endsection