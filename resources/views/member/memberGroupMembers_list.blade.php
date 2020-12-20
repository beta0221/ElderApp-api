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
        <a href="/memberGroupMembers">
            <div class="mt-4 btn btn-primary btn-block">
                顯示樹狀圖
            </div>
        </a>    
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