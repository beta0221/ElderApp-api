<!DOCTYPE html>
<html>
<head>
<title>領取活動獎勵 | {{$event->title}}</title>
<style>
    body{
        text-align: center!important;
    }
</style>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>

    <div style="height:500px">
        <h1 style="margin-top:40px">領取活動獎勵：{{$event->title}}</h1>
        <h3 style="margin-bottom:80px;">請掃描下方 QR Code 領取活動獎勵。</h3>
        <div id="reward"></div>
    </div>

    <div class="container">
        <hr>
    </div>
    
    

    <div class="container mt-4 mb-4">
        <div class="row">
            <div class="offset-md-3 col-md-6 col-sm-12">

                <h3 class="mb-4">目前活動進度：{{($event->current_day)?"第 $event->current_day 堂":"未開始"}}</h3>
                <div style="text-align:left;">
                    <p>1.課程進度一旦開啟則無法返回。</p>
                    <p>2.每堂課每位參與者只能領取一次獎勵。</p>
                </div>
                
                @for($x = 1; $x <= $event->days;$x++)
                    <div onclick="updateCurrentDay({{$x}})" class="btn btn-lg btn-info d-block mb-2 {{($event->current_day>=$x)?'disabled':''}}">第 {{$x}} 堂課</div>
                @endfor
            </div>
        </div>
    </div>


    

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script type="text/javascript" src="/js/jquery.qrcode.min.js"></script>
<script>

const maxDays = parseInt('{!!$event->current_day!!}');
const slug = '{!!$event->slug!!}';
const token = `Bearer ${localStorage.getItem('token')}`;

jQuery(function(){
    jQuery('#reward').qrcode("reward,{{$event->slug}}");
})

function updateCurrentDay(day){
    if(parseInt(day) <= maxDays){
        return
    }
    if(confirm('確認是否開啟第'+day+'堂課')){
        $.ajax({
            type: "POST",
            url: "/api/updateEventCurrentDay/"+ slug,
            data: {
                'upToDay':day,
            },
            dataType: "json",
            headers:{
                'Authorization':token
            },
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
}




</script>

</body>
</html>