<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>我的活動管理</title>
    <style>
        
    </style>
</head>
<body>
    <h2>{{$user->name}}老師的課程</h2>
    
    @foreach ($eventList as $event)
    <div style="border:1px solid gray;padding:4px 8px">
        <div style="font-size: 24px">{{$event->title}}</div>
        <a href="/event_reward_qrcode/{{$event->slug}}">
            <div style="color: #fff;background:forestgreen;display:inline-block;width:80px;padding:4px 8px;border-radius:.2rem">開啟QR碼</div>
        </a>
    </div>
    @endforeach

</body>
</html>