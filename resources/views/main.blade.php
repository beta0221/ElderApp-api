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
        {{-- @include('component.navbar') --}}
        @yield('content')
        
        @if (isset($noFooter) && $noFooter == 1)
            {{-- no footer --}}
        @else
            @include('component.footer')
        @endif
        
    </div>

</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="/js/bootstrap.js"></script>
<script>
    function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
        c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
        }
    }
    return "";
    }
    function getToken(){
        return localStorage.getItem('token');
    }
    function happyAlert(text){
        $('body').append('<div class="happy-alert"><div class="mb-4"><h3>'+text+'</h3></div><div class="happy-btn" style="background: lightgray" onclick="closeHappyAlert()">繼續購物</div><div class="happy-btn" onclick="location.href = '  +"'/cart'"+ '">前往結帳</div></div>');
    }
    function closeHappyAlert(){
        $('.happy-alert').remove();
    }
</script>
@yield('js')
<script>
    
    
</script>
</html>