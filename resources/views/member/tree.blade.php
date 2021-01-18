<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>組織樹</title>
    <style>
        * {
            position: relative;
            box-sizing: border-box;
        }

        #tree {
            margin-left: 80px;
        }

        #tree>div>div {
            border: 1px solid black;
            padding: 10px 0;
        }

        #tree .cell-user {
            border: 1px solid black;
            text-align: center;
            cursor: pointer;
        }

        #tree .cell {
            display: inline-block;
            /* text-align: center; */
            margin: 0 -0.5px;
            line-height: 20px;
            min-width: 80px;
        }

        .lv_1,.lv_2,.lv_3,.lv_4,.lv_5{
            height: 56px;
        }
        .lv_1 .cell {
            width: 80px;
        }

        .lv_5::before {
            content: '領航天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }

        .lv_4::before {
            content: '守護天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }

        .lv_3::before {
            content: '大天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }

        .lv_2::before {
            content: '小天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }

        .lv_1::before {
            content: '主人';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }
        #event-menu{
            top: 50%;
            left: calc(50% + 12px);
            position: absolute;
            display: inline-block;
            text-align: center;
            border:1px solid gray;
            width: 120px;
            z-index: 3;
        }
        li.user-name{
            background:teal;
            color: #fff;
        }
        li.event{
            cursor: pointer;
            background: #fff;
        }
        li.event:hover{
            background: lightgray;
        }
        ul{
            padding: 0;
            margin:0;
        }
        ul li{
            list-style: none;
            height: 32px;
            line-height: 32px;
            color: #000;
        }
        .session-card{
            font-size: 24px;
            border-radius: .3rem;
            position: fixed;
            top: 8px;
            right: 12px;
            padding: 8px 16px;
            z-index: 5;
            color: #fff;
        }
    </style>
</head>

<body>
    <div>
        <button onclick="exportExcel()" style="color:#fff;background:#4caf50;padding:4px 8px;cursor:pointer;border-radius:.3rem">匯出excel</button>
    </div>

    @if(Session::has('error'))
        <div style="background:orange" class="session-card">{{Session::get('error')}}</div>
    @endif
    @if(Session::has('success'))
        <div style="background:limegreen" class="session-card">{{Session::get('success')}}</div>
    @endif
    <div style="margin-right:160px;margin-top:40px">
        <div id="tree">
            <div class="lv_5"></div>
            <div class="lv_4"></div>
            <div class="lv_3"></div>
            <div class="lv_2"></div>
            <div class="lv_1"></div>
        </div>
    </div>

    <form style="display:none" id="action-form" action="/removeMemberFromGroup" method="POST">
        {{ csrf_field() }}
        <input id="token-input" type="text" name="token">
        <input id="user-id-input" type="test" name="user_id">
    </form>

</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script>

    var UserId = {{$user_id}};
    var group_users = {!!$group_users!!};
    var name_dic = {!!$name_dic!!};
    var valid_dic = {!!$valid_dic!!};
    var temp = {};
    var dic = {};
    var targetUserId = null;
    $(document).ready(function () {

        group_users.forEach(function (item, index) {


            //把結構放進html
            for (let i = 5; i > 0; i--) {
                
                if (item.level == 5) {
                    var target = $(`div.lv_${i}`);
                    let cell = document.createElement('div');
                    $(cell).addClass('cell');
                    $(cell).addClass(`user_${item.user_id}`);
                    if (item.level == i) {
                        $(cell).html(`${name_dic[item.user_id]}`);
                        $(cell).addClass('cell-user');
                        $(cell).attr('user_id',item.user_id);
                    }
                    target.append(cell);
                } else {

                    for(let l = item.level +1;l<=5;l++){

                        if(item[`lv_${l}`] != null){
                            var target = $(`.lv_${i} .user_${item['lv_' + l]}`);

                            if (i <= item.level) {
                                //console.log({ 'lv_i': i, 'l': l });
                                let cell = document.createElement('div');
                                $(cell).addClass('cell');
                                $(cell).addClass(`user_${item.user_id}`);
                                if (item.level == i) {
                                    $(cell).html(`${name_dic[item.user_id]}`);
                                    $(cell).addClass('cell-user');
                                    $(cell).attr('user_id',item.user_id);
                                }
                                target.append(cell);
                            }
                            break;
                        }
                        if(l == 5 && item['lv_5'] == null){
                            if (item.level >= i) {
                                var target = $(`div.lv_${i}`);
                                let cell = document.createElement('div');
                                $(cell).addClass('cell');
                                $(cell).addClass(`user_${item.user_id}`);
                                if(item.level == i){
                                    $(cell).html(`${name_dic[item.user_id]}`);
                                    $(cell).addClass('cell-user');
                                    $(cell).attr('user_id',item.user_id);
                                }
                                target.append(cell);
                            }
                        }
                    }

                }

            }

        });

        //計算寬度
        group_users.forEach(function (row, index) {
            let id = row.user_id;
            $('.user_'+id).each(function(index,element){

                var count = getChildrenLength($(this));
                if(!dic['user_'+id] || dic['user_'+id] < count){
                    dic['user_'+id] = count;
                }
            });
        });


        //照著dic把寬度放進html
        Object.keys(dic).forEach(function (key) {
            var width = 80;
            if(dic[key] > 0){
                width = 80 * dic[key];
            }
            $(`.${key}`).css('width', `${width}px`);
        });

        //給主角顏色
        $('.cell-user.user_'+UserId).css({'background':'#2196f3','color':'#fff'});
        //效期顏色
        Object.keys(valid_dic).forEach(function(key){
            if(valid_dic[key] != 1){
                $('.cell-user.user_'+key).css({'color':'Red'});
            }
        });

        setTimeout(function(){ 
            $('.session-card').remove();
        }, 2000);

    });

    function getChildrenLength(element){
        var count = 0
        if($(element).children().length == 0){
            return 1;
        }else{
            $(element).children().each(function(index,value){
                count += getChildrenLength(value);
            });
            return count;
        }
    }

    $(document).mousedown(function(event) {
        if($('.event').is(event.target)){
            return;
        }
        $('#event-menu').remove();
        var specificDiv= $(".cell-user");
        if (specificDiv.is(event.target)){
            let user_id = $(event.target).attr('user_id');
            let user_name = name_dic[user_id];
            $(event.target).append("<div id='event-menu'><ul><li class='user-name'>"+user_name+"</li><li id='moveMember' class='event'>移動</li><li id='removeMemberFromGroup' class='event'>從組織移除</li></ul></div>");
            $('#user-id-input').val(user_id);
            targetUserId = user_id;
        }
    });

    $(document).on('click','#removeMemberFromGroup',function(){
        if(!confirm('確定將此使用者從組織移除')){
            return;
        }
        $('#token-input').val(localStorage.getItem('token'));
        $('#action-form').submit();
    });

    $(document).on('click','#moveMember',function(){
        if(!confirm('確定移動此會員')){
            return;
        }
        window.location.href = '/moveMemberPage/'+targetUserId;
    });
    
    function exportExcel(){
        window.open('/excel/memberGroupMembers?token='+localStorage.getItem('token') + '&user_id='+ UserId);
    };
</script>

</html>