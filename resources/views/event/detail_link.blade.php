@extends('universal')

@section('title','| ' . $event->title)

@section('css')

@endsection

@section('content')
<div class="container">
    
    <div class="row">
        <div class="col-sm-12">
            <img class="w-100" src="{{$event->imgUrl}}">
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <h2 class="mb-3">目前人數：{{$event->people}}</h2>
            <h2 class="mb-3">地點：{{$event->location}}</h2>
            <h3 class="mb-3">獎勵：{{$event->reward}}</h3>
        </div>
    </div>
    
    <div class="row">
        <div class="col-sm-12">
            <hr>
            <p>
                {!! nl2br($event->body)  !!}
            </p>
        </div>
    </div>

</div>
@endsection

@section('js')

@endsection