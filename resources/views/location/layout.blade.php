<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>據點管理 @yield('title')</title>
    <style>
        *{
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            position: relative;
        }
        .outter{
            padding: 24px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            align-self: center;
            max-width: 600px;
            width: 100%;
            height: 100vh;
            border:.5px solid lightgray;
            overflow-y: scroll;
        }
        .data-cell{
            background:#fb8c00;
            color: #fff;
            padding: 12px;
            margin-bottom: 12px;
            font-size: 28pt;
            border-radius: .3rem;
        }
        a{
            text-decoration: none;
            padding:0 8px;
            color: #fff;
            border-radius: .3rem;
        }
    </style>
    @yield('css')
</head>
<body>
    <div class="outter">
        @yield('content')
    </div>
</body>

@yield('js')
</html>