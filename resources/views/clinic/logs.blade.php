<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>志工服務記錄</title>

    <style>
        body{
            text-align: center!important;
        }
        .cell{
            text-align: left;
        }
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-4 mb-4">
        <div class="row">
            <div class="offset-md-3 col-md-6 col-sm-12">
                
                <div class="text-left mb-2">
                    <a href="/clinic/all" class="btn btn-sm btn-secondary">回上頁</a>
                </div>

                <h3>{{$clinic->name}}</h3>

                <div class="mb-2">
                    <form action="/{{Request::path()}}" method="GET">
                        <div class="form-row">
                            <div class="col-6">
                                <input type="text" class="form-control" placeholder="姓名" name="user_name" value="{{(Request::has('user_name'))?Request::get('user_name'):''}}">
                            </div>
                            <div class="col-3">
                                <button class="btn btn-primary btn-block">搜尋</button>
                            </div>
                            <div class="col-3">
                                <a class="btn btn-secondary btn-block" href="/{{Request::path()}}">清除</a>
                            </div>
                        </div>
                    </form>
                </div>

                {{$links}}
                
                @if (count($logs) == 0)
                    <div class="cell alert alert-secondary">
                        <span>目前尚無服務記錄</span>
                    </div>    
                @endif

                @foreach ($logs as $i => $log)
                    <?php $log = (object)$log ?>
                    @if ($log->is_complete)
                    <div class="cell alert alert-primary">
                        <h5>{{$log->user_name}}</h5>
                        <h6>時數：{{$log->total_hours}}</h6>
                        <span>{{$log->created_at}} ~ {{$log->complete_at}}</span>
                    </div>    
                    @endif
                
                @endforeach

                {{$links}}
            </div>
        </div>
    </div>
</body>
</html>