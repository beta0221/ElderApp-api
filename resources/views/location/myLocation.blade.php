@extends('location.layout')

@section('title',"| 我的據點")

@section('css')
<style>

</style>

@endsection

@section('content')

@foreach ($locations as $location)
<div class="data-cell">
    <div style="margin-bottom: 8px">{{$location->name}}</div>
    <a href="/order-list/location/{{$location->slug}}" style="background:#f44336 ">兌換區</a>
    <a href="/view_locationOrderList/{{$location->slug}}" style="background: darkgreen">商城訂單</a>
</div>

    
@endforeach

@endsection

@section('js')

@endsection