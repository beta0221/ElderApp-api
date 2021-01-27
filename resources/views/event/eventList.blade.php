<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>我的活動管理</title>
    <style>
        .btn{
            font-size: 24px;
            color: #fff;display:inline-block;padding:4px 8px;border-radius:.2rem;
        }
        .bg-g{
            background:rgb(63, 141, 63);
        }
        .bg-b{
            background:rgb(83, 177, 214);
        }
        .bg-r{
            background:rgb(214, 44, 109);
        }
    </style>
</head>
<body>
    <h2 style="color:gray">{{$user->name}} 老師的課程</h2>
    
    @foreach ($eventList as $event)
    <div style="border:1px solid gray;padding:4px 8px;border-radius:.2rem;margin-bottom:8px">
        <div style="font-size: 24px">{{$event->title}}</div>
        <div>
            <span>狀態:</span>
            <span style="color:{{($event->public)?'forestgreen':'gray'}}">{{($event->public)?'上架中':'已下架'}}</span>
        </div>


        @if ($event->public)
        <div class="btn bg-r" onclick="updateEventStatus({{$event->id}},0)">下架</div>
        @else
        <div class="btn bg-b" onclick="updateEventStatus({{$event->id}},1)">上架</div>
        @endif
        
        

        <a href="/event_reward_qrcode/{{$event->slug}}">
            <div class="btn bg-g">開啟QR碼</div>
        </a>
    </div>
    @endforeach

    @if (count($eventList) <= 0)
        <h2>目前無課程</h2>
    @endif

</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script>
function updateEventStatus(event_id,public){
    $.ajax({
            type: "POST",
            url: "/api/updateEventPublicStatus",
            data: {
                'public':public,
                'event_id':event_id,
            },
            dataType: "json",
            success: function (response) {
                if(response.s==1){
                    window.location.reload();
                }else{
                    alert(response.m);
                }
            },
            error:function(error){
                alert(error);
            }
        });
}
</script>
</html>