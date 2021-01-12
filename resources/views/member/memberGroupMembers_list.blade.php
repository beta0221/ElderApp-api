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
    .switcher-selected{
        display: block;background:#fff;margin:8px;border-radius: 3px;line-height:48px;text-decoration: none;color:gray
    }
    .switcher-unselected{
        display: block;background:#007bff;margin:8px;border-radius: 3px;color:#fff;
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

        @if ($showTreeButton)
        <div class="mt-2" style="background: #007bff;width:99.5%;text-align:center;border-radius: 3px">
            <div style="display: inline-block;width:50%">
                <span class="switcher-selected">顯示列表</span>
            </div><div style="display: inline-block;width:50%">
                <a href="/memberGroupMembers" class="switcher-unselected">
                    顯示樹狀
                </a>
            </div>
        </div>    
        @endif
        
        <h3 class="mt-2 mb-2">組織人員表</h3>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                      <tr>
                        
                        <th scope="col">職位</th>
                        <th scope="col">姓名</th>
                        <th scope="col">會員資格</th>
                        <th scope="col">會員效期</th>
                        <th scope="col">推薦人</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $i => $user)
                            <tr>
                                
                                <td>{{(isset($rank[$user->org_rank]))?$rank[$user->org_rank]:'主人'}}</td>
                                <td>{{$user->name}}</td>
                                <td class="{{($user->valid)?'text-success':'text-danger'}}">{{($user->valid)?'有效':'無效'}}</td>
                                <td>{{$user->expiry_date}}</td>
                                <td>{{$user->inviter}}</td>
                            </tr>   
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

     

    </div>
</body>

<script>
   
</script>
</html>