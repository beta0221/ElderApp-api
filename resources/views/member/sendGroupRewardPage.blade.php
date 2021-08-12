<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <a style="color: #fff;background:gray;border-radius:.3rem;padding: 4px 8px;" href="javascript:history.back()">返回</a>
    <h3>補發組織獎勵</h3>
    <h3>成員：{{$user->name}}</h3>
    @foreach ($aboveGroupUsers as $level => $name)
        <span>{{$level}}：{{$name}}</span><br>
    @endforeach

    <form action="/sendGroupReward/{{$user->id}}" method="POST">
        {{ csrf_field() }}

        <span>補發項目：</span>
        <select name="action">
            <option value="">選擇補發項目</option>
            <option value="renew">續會</option>
            <option value="join">新入會</option>
        </select>
        <button>確認發送</button>
    </form>

</body>
</html>