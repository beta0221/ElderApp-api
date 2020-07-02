<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <link rel="stylesheet" href="/css/bootstrap.css">
    <link rel="stylesheet" href="/css/common.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>銀髮學院 @yield('title')</title>
</head>
@yield('css')
<body>
    <div class="wrapper">
        @include('component.navbar')
        @yield('content')
        @include('component.footer')
    </div>

</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="/js/bootstrap.js"></script>
<script>
    function getToken(){
        return localStorage.getItem('token');
    }
    function happyAlert(text){
        $('body').append('<div class="happy-alert"><div><h3>'+text+'</h3></div><div class="happy-btn" onclick="closeHappyAlert()">確定</div></div>');
    }
    function closeHappyAlert(){
        $('.happy-alert').remove();
    }
</script>
@yield('js')
<script>
    
    
</script>
</html>