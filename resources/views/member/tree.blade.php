<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        #tree td{
            border:1px solid black;
            text-align: center;
            padding: 0;
            margin: 0;
            height: 40px;
            min-width:80px;
        }
        .lv_1 td{
            width:80px;
        }
    </style>
</head>
<body>
    <div>
        <table id="tree">
            <tr class="lv_5"></tr>
            <tr class="lv_4"></tr>
            <tr class="lv_3"></tr>
            <tr class="lv_2"></tr>
            <tr class="lv_1"></tr>
        </table>
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
                        var target = $(`tr.lv_${i}`);
                        let td = document.createElement('td');
                        $(td).addClass(`user_${item.user_id}`);
                        if(item.level == i){
                            $(td).html(`${name_dic[item.user_id]}`);
                        }
                        target.append(td);
                    }else{
                        var l = item.level+1
                        while (item[`lv_${l}`]==null || l>5) {
                            l++;
                        }
                        var target = $(`.lv_${i} .user_${item['lv_'+l]}`);
                                
                        if(i <= item.level){
                            console.log({'lv_i':i,'l':l});
                            let td = document.createElement('td');
                            $(td).addClass(`user_${item.user_id}`)
                            if(item.level == i){    
                                $(td).html(`${name_dic[item.user_id]}`);
                            }
                            target.append(td);
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