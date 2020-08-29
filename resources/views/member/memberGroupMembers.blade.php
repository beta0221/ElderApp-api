<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/bootstrap.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>組織人員</title>
</head>
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
        left: 80%;
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
</style>
<?php
    $rank = [
        1=>'主人',
        2=>'小天使',
        3=>'大天使',
        4=>'守護天使',
        5=>'領航天使'
    ];
?>
<body>
    <div class="container">

        <h3 class="mt-2 mb-2">組織人員表</h3>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">姓名</th>
                        <th scope="col">電話</th>
                        <th scope="col">地區</th>
                        <th scope="col">入會日期</th>
                        <th scope="col">會員效期</th>
                        <th scope="col">會員資格</th>
                        <th scope="col">職位</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $i => $user)
                            <tr>
                                <th scope="row">{{($i+1)}}</th>
                                <td>{{$user->name}}</td>
                                <td>{{$user->phone}}</td>
                                <td>{{(isset($districtDict[$user->district_id]))?$districtDict[$user->district_id]:''}}</td>
                                <td>{{substr($user->created_at,0,10)}}</td>
                                <td>{{$user->expiry_date}}</td>
                                <td class="{{($user->valid)?'text-success':'text-danger'}}">{{($user->valid)?'有效':'無效'}}</td>
                                <td>{{(isset($rank[$user->org_rank]))?$rank[$user->org_rank]:'主人'}}</td>
                            </tr>   
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <h3 class="mt-2 mb-2">組織樹狀圖</h3>

        <div class="row mb-4">
            <div class="col-md-12">

                <div style="overflow-x: scroll;border:1px solid gray">
                    <div id="tree">
                        <div class="lv_5"></div>
                        <div class="lv_4"></div>
                        <div class="lv_3"></div>
                        <div class="lv_2"></div>
                        <div class="lv_1"></div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script>
    var group_users = {!!$group_users!!};
    var name_dic = {!!$name_dic!!};
    var valid_dic = {!!$valid_dic!!};
    var dic = {};
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
        //效期顏色
        Object.keys(valid_dic).forEach(function(key){
            if(valid_dic[key] != 1){
                $('.cell-user.user_'+key).css({'color':'Red'});
            }
        });
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
</script>
</html>