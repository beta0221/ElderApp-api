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

        .lv_1 .cell {
            width: 80px;
        }

        .lv_5::before {
            content: '長老天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }

        .lv_4::before {
            content: '領航天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }

        .lv_3::before {
            content: '守護天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }

        .lv_2::before {
            content: '大天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%, -50%);
        }

        .lv_1::before {
            content: '小天使';
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
    console.log(group_users);
    console.log(name_dic);
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
                    var l = item.level + 1
                    while (item[`lv_${l}`] == null || l > 5) {
                        l++;
                    }
                    var target = $(`.lv_${i} .user_${item['lv_' + l]}`);

                    if (i <= item.level) {
                        console.log({ 'lv_i': i, 'l': l });
                        let cell = document.createElement('div');
                        $(cell).addClass('cell');
                        $(cell).addClass(`user_${item.user_id}`)
                        if (item.level == i) {
                            $(cell).html(`${name_dic[item.user_id]}`);
                            $(cell).addClass('cell-user');
                        }
                        target.append(cell);
                    }

                }

            }


            //計算寬度
            group_users.forEach(function (row, index) {

                var key = '';
                for (let j = row.level; j <= item.level; j++) {

                    let lv = `lv_${j}`;
                    if (row[lv]) {
                        if (!key) {
                            key = key + row[lv];
                        } else {
                            key = key + '-' + row[lv];
                        }
                    } else {
                        if (!key) {
                            key = key + '0';
                        } else {
                            key = key + '-' + '0';
                        }
                    }
                }

                if (key) {
                    var k_array = key.split('-');
                    if (k_array[k_array.length - 1] == item.user_id) {

                        if (k_array.length != 1) {

                            var new_array = [];
                            for (let z = 1; z < k_array.length; z++) {
                                new_array.push(k_array[z]);//拿掉最前面一個
                            }
                            new_array = new_array.join('-');

                        } else {
                            new_array = key;
                        }

                        if (!temp[new_array]) {
                            if (dic[item.user_id]) {
                                dic[item.user_id] += 1;
                            } else {
                                dic[item.user_id] = 1;
                            }
                        }

                        temp[key] = true;
                        temp[new_array] = true;
                    }

                }

            });

        });

        //照著dic把寬度放進html
        Object.keys(dic).forEach(function (key) {
            let width = 80 * dic[key];
            $(`.user_${key}`).css('width', `${width}px`);
        });

        //給主角顏色
        $('.cell-user.user_'+UserId).css({'background':'#2196f3','color':'#fff'});

    });
</script>

</html>