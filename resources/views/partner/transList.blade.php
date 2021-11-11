<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>特約商店入帳紀錄</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    

    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand" href="#">{{$user->name}} - 入帳紀錄</a>
    </nav>

    {{-- <div class="pt-3 pl-2 pr-2">
        {{$links}}
    </div> --}}

    <div>
        
        @foreach ($transList as $tran)
        <?php $tran = (object)$tran; ?>
            <div class="card m-2">
                <div class="card-body">

                    <h5 class="card-title">{{$tran->target_name}}</h5>
                    <h6 class="card-subtitle mb-2 text-muted">{{$tran->created_at}}</h6>
                    <h4 class="card-title {{($tran->give_take==1)?'text-success':'text-danger'}}">{{$tran->amount}}</h4>
                    <span>{{$tran->event}}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="pt-2 pl-2 pr-2">
        {{$links}}
    </div>

</body>
</html>