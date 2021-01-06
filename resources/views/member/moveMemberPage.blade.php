<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>移動成員</title>
    <style>
        *{
            font-size: 20px;
        }
    </style>
</head>
<body>
<a style="color: #fff;background:gray;border-radius:.3rem;padding: 4px 8px;" href="javascript:history.back()">返回</a>
<h3>移動成員：{{$user->name}}</h3>

@if($isLeader)
    @if ($app)
    <h3>此使用者無法移動</h3>
    @else
        <form action="/promoteLeader" method="POST">
            {{csrf_field()}}
            <input type="hidden" name="user_id" value="{{$user->id}}">
            <label for="">提升職位</label>
            <select name="target_level">
                <option value="">選擇職位</option>
                <option value="5">領航天使</option>
                <option value="4">守護天使</option>
                <option value="3">大天使</option>
            </select>
            <button type="submit">確定提升</button>
        </form>
    @endif
@else

<form action="/moveMember/{{$user->id}}" method="POST">
    {{csrf_field()}}
    @if ($app)
        <input type="hidden" name="app" value="1">
    @endif
    <label for="">移動至：</label>
    <select name="target_user_id" id="user-selector">
        <option value="">選擇成員</option>
        @foreach($group_users as $g_user)
            @if($g_user->id != $user->id)
                <option value="{{$g_user->id}}">{{$g_user->name}}</option>
            @endif
        @endforeach
    </select><br><br>

    @if ($app)
        <input type="hidden" name="target_level" value="1">
    @else
        <label for="">下階層職位：</label>
        <select name="target_level" id="">
            <option value="">選擇職位</option>
            <option class="option-level option-level-4" value="4">守護天使</option>
            <option class="option-level option-level-3" value="3">大天使</option>
            <option class="option-level option-level-2" value="2">小天使</option>
            <option class="option-level option-level-1" value="1">主人</option>
        </select><br><br>
    @endif
    <button type="submit">確定移動</button>
</form>

@endif

</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script>
        var levelDict = {!!$levelDict!!};
        $(document).ready(function(){

            $('#user-selector').on('change',function(){
                let user_id = $(this).val();
                if(user_id){
                    let level = levelDict[user_id];
                    $('.option-level').hide();
                    for (let i = 0; i < level; i++) {
                        $('.option-level-'+i).show();
                    }
                }
            })

        }); 





    </script>
</html>