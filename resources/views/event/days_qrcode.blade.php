<!DOCTYPE html>
<html>
<head>
<title>領取活動獎勵 | {{$event->title}}</title>
<style>
    body{
        text-align: center!important;
    }
    .alert-box{
        position: fixed;
        top: 30%;
        left: 50%;
        transform: translateX(-50%);
        background: #fff;
        border:1px solid gray;
        border-radius: .3rem;
        min-width: 240px;
        width: 80%;
        padding: 24px;
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
                    <div onclick="confirmUpdateCurrentDay({{$x}})" class="btn btn-lg d-block mb-2 {{($event->current_day>=$x)?'disabled btn-secondary':'btn-info'}}">第 {{$x}} 堂課</div>
                @endfor
            </div>
        </div>
    </div>


    <div class="alert-box confirm" style="display: none">
        <div>
            <h1>訊息</h1>
        </div>
        <div class="mt-4 mb-4" style="font-size: 32px">
            <span id="message">訊息</span>
        </div>
        <div>
            <div onclick="cancleDialog()" class="btn btn-secondary btn-lg" style="display:inline-block;width:49%">取消
            </div><div style="display: inline-block;width:2%">
            </div><div onclick="updateCurrentDay()" class="btn btn-primary btn-lg" style="display:inline-block;width:49%">確定</div>
        </div>
    </div>

    <div class="alert-box my-alert" style="display: none;">
        <div>
            <h2>訊息</h2>
        </div>
        <div class="mt-4 mb-4" style="font-size: 24px">
            <span id="message">訊息</span>
        </div>
        <div>
            <div onclick="cancleDialog()" class="btn btn-primary btn-lg" style="display: block">確定</div>
        </div>
    </div>

    

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script type="text/javascript" src="/js/jquery.qrcode.min.js"></script>
<script>

const maxDays = parseInt('{!!$event->current_day!!}');
const slug = '{!!$event->slug!!}';
//const token = `Bearer ${localStorage.getItem('token')}`;

jQuery(function(){
    jQuery('#reward').qrcode("reward,{{$event->slug}}");
});

var updateDay = null;
function confirmUpdateCurrentDay(day){
    updateDay = day;
    $('.confirm #message').html('確認是否開啟第 ' + day + ' 堂課');
    $('.confirm').show();
};

function cancleDialog(){
    $('.alert-box').hide();
};

function customeAlert(message){
    $('.my-alert #message').html(message);
    $('.my-alert').show();
};

function updateCurrentDay(){
    if(parseInt(updateDay) <= maxDays){ return; }
    cancleDialog();
    $.ajax({
        type: "POST",
        url: "/api/updateEventCurrentDay/"+ slug,
        data: {
            'upToDay':updateDay,
        },
        dataType: "json",
        // headers:{
        //     'Authorization':token
        // },
        success: function (response) {
            if(response.s==1){
                window.location.reload();
            }else{
                customeAlert(response.m);
            }
        },
        error:function(error){
            customeAlert(error);
        }
    });


};




</script>

</body>
</html>