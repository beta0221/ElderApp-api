<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>我的診所</title>
    <style>
        body{
            text-align: center!important;
        }
        .clinic-cell{
            text-align: left;
        }
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-4 mb-4">
        <div class="row">
            <div class="offset-md-3 col-md-6 col-sm-12">
                @foreach ($clinicList as $i => $clinic)
                    <div class="clinic-cell alert alert-primary">
                        <button class="btn btn-sm btn-primary">{{$i+1}}</button>
                        <span class="ml-1 mr-1">{{$clinic->name}}</span>
                        <a class="float-right btn btn-sm btn-primary text-white"
                            href="/clinic/{{$clinic->slug}}/manage">管理</a>
                    </div>
                    
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>