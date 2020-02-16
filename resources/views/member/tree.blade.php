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
            content: '平民';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }
    </style>
</head>

<body>
    <div>
        <div id="tree">
            <div class="lv_5"></div>
            <div class="lv_4"></div>
            <div class="lv_3"></div>
            <div class="lv_2"></div>
            <div class="lv_1"></div>
        </div>
    </div>

    

</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script>

    var UserId = {{$user_id}};
    var group_users = {!!$group_users!!};
    var name_dic = {!!$name_dic!!};
    //console.log(group_users);
    //console.log(name_dic);
    var temp = {};
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