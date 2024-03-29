<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>組織人員</title>
    <style>
        *{
            position: relative;
        }
        body{
            margin: 8px 2px;
        }
        th{
            width: calc(100% / 5);
        }
        td{
            text-align: center;
            border:1.5px solid darkgray;
            padding: 12px 0;
        }
        p{
            color: gray;
            margin: 8px 0;
        }
        .p-data{
            color: rgba(0,0,0,0.7);
            font-size:28px;
            
        }
        .data-name{
            color: black;
            font-size: 32px;
        }
        .unvalid{
            color: #721c24;
            background-color: #f8d7da;
            border: 1.5px solid #f5c6cb;
        }
        .empty{
            border:0.5px solid lightgray;
        }
        .user-detail{
            position: fixed;
            width: 100%;
            height: 100vh;
            top: 0;
            left: 0;
            background: rgba(0,0,0,0.1);
            z-index: 10;   
        }
        .user-detail-content{
            position: absolute;
            width: calc(100% - 24px);
            height: calc(100% - 24px);
            left: 50%;
            top: 50%;
            background:#fff;
            transform: translate(-50%,-50%);
            border-radius: .3rem;
            overflow-y: scroll;
        }
        .user-detail-cancel{
            position: absolute;
            padding: 2px 4px;
            border-radius: .2rem;
            top: 12px;
            right: 12px;
            font-size: 24px;
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
        .switcher-selected{
            display: block;background:#fff;margin:8px;border-radius: 3px;line-height:48px;text-decoration: none;color:gray
        }
        .switcher-unselected{
            display: block;background:#007bff;margin:8px;border-radius: 3px;color:#fff;text-decoration: none;
        }
        .primary-btn{
            cursor: pointer;
            margin-top: .5rem !important;
            margin-bottom: .5rem !important;
            display: block;
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

    </style>
</head>

<body>
    @if(Session::has('error'))
        <div style="background:orange" class="session-card">{{Session::get('error')}}</div>
    @endif
    @if(Session::has('success'))
        <div style="background:limegreen" class="session-card">{{Session::get('success')}}</div>
    @endif


    <div class="user-detail" style="display: none">
        <div class="user-detail-content">
            <div style="padding: 0 8px;">
                
                <p class="data-name"></p>
                <hr>
                <div class="primary-btn" onclick="moveMember()">移動組員</div>
                <p>手機：</p>
                <p class="p-data data-phone"></p>
                <p>推薦人：</p>
                <p class="p-data data-inviter"></p>
                <p>入會日期：</p>
                <p class="p-data date-created_at"></p>
                <p>會員效期：</p>
                <p class="p-data data-valid"></p>
                <p>效期到期日：</p>
                <p class="p-data data-expiry_date"></p>
            </div>
            
            <div class="user-detail-cancel" onclick="cancel()">關閉</div>
        </div>
    </div>

    <div style="background: #007bff;width:99.5%;text-align:center;border-radius: 3px">
        <div style="display: inline-block;width:50%">
            <a href="/memberGroupMembers_list" class="switcher-unselected">
                顯示列表
            </a>
        </div><div style="display: inline-block;width:50%">
            <span class="switcher-selected">顯示樹狀</span>
        </div>
    </div>
    

    <div>
        <div>
            <span>總人數：</span><span>{{$total_amount}}</span>
        </div>
        <div>
            <span>有效：</span><span style="color: forestgreen">{{$valid_amount}}</span>
        </div>
        <div>
            <span>無效：</span><span style="color: indianred">{{$invalid_amount}}</span>
        </div>
    </div>

    <table style="width: 100%">
        <thead>
            <tr>
                <th>領航天使</th>
                <th>守護天使</th>
                <th>大天使</th>
                <th>小天使</th>
                <th>主人</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>

</body>

<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script>
    var group_users = {!!$group_users!!};
    var name_dic = {!!$name_dic!!};
    var valid_dic = {!!$valid_dic!!};
    var rowspanDict = {};
    
    var maxLevel;
    var hasSelected = {};
    var detailUserId;

    $(document).ready(function(){

        group_users.sort(function(a,b){
            for (let lv = 5; lv >= 2; lv--) {
                let _lv = 'lv_' + lv;
                if (a[_lv] > b[_lv]) {
                    return -1;
                }
                if (a[_lv] < b[_lv]) {
                    return 1;
                }
            }
            return 0;
        });
        
        maxLevel = group_users[0]['level'];
        genRowspanDict();

        for (let lv = 5; lv >= 1; lv--) {
            group_users.forEach(function (item, index) {
                if(item.level == lv){
                    addRow(item);        
                }       
            });
        }

        mergeRows();

        $('td').on('click',function(){
            let user_id = $(this).attr('user_id');
            if(user_id){
                showUserDetail(user_id);
            }
        });
        
        setTimeout(function(){ 
            $('.session-card').remove();
        }, 2000);
        
    });

    function cancel(){
        $('.user-detail').hide();
    }

    function moveMember(){
        if(!detailUserId){ return; }
        window.location.href = '/moveMemberPage/' + detailUserId + '?app=true';
    }

    function showUserDetail(user_id){
        detailUserId = user_id;
        $.ajax({
            type: "GET",
            url: "/getGroupMemberDetail/"+user_id,
            dataType: "json",
            success: function (res) {
                $('.user-detail').show();
                $('.data-name').html(res.name);
                $('.data-phone').html(res.phone);
                $('.data-inviter').html(res.inviter);
                $('.date-created_at').html(res.created_at);
                
                if(res.expiry_date){
                    $('.data-expiry_date').html(res.expiry_date);
                }else{
                    $('.data-expiry_date').html('無');
                }
                if(res.valid == 1){
                    $('.data-valid').html('<font color="forestgreen">有效</font>');
                }else{
                    $('.data-valid').html('<font color="#d34a4a">無效</font>');
                }
            }
        });
    }

    function mergeRows(){

        Object.keys(rowspanDict).forEach(key =>{
            $('.user_'+key).each(function(index){
                if(index == 0){
                    $(this).attr('rowspan',rowspanDict[key]);
                }else{
                    $(this).remove();
                }
            });
        });
        
    }

    function genRowspanDict(){
        group_users.forEach(function(item,index){
            for (let lv = 5; lv >= 1; lv--) {
                let user_id = item['lv_'+lv];
                if(user_id){
                    if(rowspanDict.hasOwnProperty(user_id)){
                        rowspanDict[user_id] += 1;
                    }else{
                        rowspanDict[user_id] = 1;
                    }
                }
            }
        });
    }

    function selectGroupUsers(level,user_id){
        let users = [];
        group_users.forEach(function(item,index){
            if(item[level] == user_id){
                users.push(item);
                hasSelected[item.user_id] = true;
            }
        });
        return users;
    }

    function addRow(item){

        if(item.level == maxLevel){ return; }
        if(hasSelected[item.user_id]){ return; }
        let lv = 'lv_' + item.level;
        let _group_users = selectGroupUsers(lv,item.user_id);

        
        _group_users.forEach(element => {
            
            let tr = document.createElement('tr');

            for (let lv = 5; lv >= 1; lv--) {
                
                let user_id = element['lv_'+lv];
                let td = document.createElement('td');

                if(!user_id){
                    $(td).html('　');
                    $(td).addClass('empty');
                    tr.append(td);
                    continue;    
                }            

                $(td).addClass('user_'+user_id);
                if(valid_dic[user_id] == 0){
                    $(td).addClass('unvalid');
                }
                $(td).html(name_dic[user_id]);
                $(td).attr('user_id',user_id);
                tr.append(td);
            }

            $('tbody').append(tr);
        });
        

    }


</script>
</html>