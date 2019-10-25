<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>組織樹</title>
    <style>
        *{
            position: relative;
        }
        #tree{
            margin-left: 80px;
        }
        #tree > div > div{
            border:1px solid black;
            padding: 10px 0;
        }
        #tree .cell{
            display: inline-block;
            text-align: center;
            margin: 0;
            line-height: 20px;
            min-width:80px;
        }
        .lv_1 .cell{
            width:80px;
        }
        .lv_5::before{
            content:'長老天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%,-50%);
        }
        .lv_4::before{
            content:'領航天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%,-50%);
        }
        .lv_3::before{
            content:'守護天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%,-50%);
        }
        .lv_2::before{
            content:'大天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%,-50%);
        }
        .lv_1::before{
            content:'小天使';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translate(-100%,-50%);
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
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
  <script>
    var group_users = {!!$group_users!!};
    var name_dic = {!!$name_dic!!};
      console.log(group_users);
      console.log(name_dic);
      var dic = {};
      $(document).ready(function(){

        group_users.forEach(function(item,index){

            for (let i = 5; i > 0; i--) {
                    
                    if(item.level==5){
                        var target = $(`div.lv_${i}`);
                        let cell = document.createElement('div');
                        $(cell).addClass('cell');
                        $(cell).addClass(`user_${item.user_id}`);
                        if(item.level == i){
                            $(cell).html(`${name_dic[item.user_id]}`);
                        }
                        target.append(cell);
                    }else{
                        var l = item.level+1
                        while (item[`lv_${l}`]==null || l>5) {
                            l++;
                        }
                        var target = $(`.lv_${i} .user_${item['lv_'+l]}`);
                                
                        if(i <= item.level){
                            console.log({'lv_i':i,'l':l});
                            let cell = document.createElement('div');
                            $(cell).addClass('cell');
                            $(cell).addClass(`user_${item.user_id}`)
                            if(item.level == i){    
                                $(cell).html(`${name_dic[item.user_id]}`);
                            }
                            target.append(cell);
                        }                      
                        
                    }

            }


            //計算寬度
            if(item.level == 1){
                for (let i = 2; i <= 5; i++) {
                    if(item[`lv_${i}`]){
                        if(dic[item[`lv_${i}`]]){
                            dic[item[`lv_${i}`]] += 1;
                        }else{
                            dic[item[`lv_${i}`]] = 1;
                        }
                    }        
                }
            }

        
        });


        Object.keys(dic).forEach(function(key){
            let width = 80 * dic[key];
            $(`.user_${key}`).css('width',`${width}px`);
        });




      });
  </script>
</html>