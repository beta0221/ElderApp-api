<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>移動成員</title>
</head>
<body>
    

<h3>移動成員：{{$user->name}}</h3>
<form action="/moveMember/{{$user->id}}" method="POST">
    {{csrf_field()}}
    <label for="">移動至：</label>
    <select name="target_user_id" id="user-selector">
        <option value="">選擇成員</option>
        @foreach($group_users as $g_user)
            @if($g_user->id != $user->id)
                <option value="{{$g_user->id}}">{{$g_user->name}}</option>
            @endif
        @endforeach
    </select>

    <label for="">下階層職位：</label>
    <select name="target_level" id="">
        <option value="">選擇職位</option>
        <option class="option-level option-level-4" value="4">守護天使</option>
        <option class="option-level option-level-3" value="3">大天使</option>
        <option class="option-level option-level-2" value="2">小天使</option>
        <option class="option-level option-level-1" value="1">主人</option>
    </select>
    <button type="submit">確定移動</button>
</form>




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