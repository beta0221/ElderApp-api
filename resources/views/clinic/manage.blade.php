<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>志工管理</title>


    <style>
        body{
            text-align: center!important;
        }
        .user-cell{
            text-align: left;
        }
    </style>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    

    <h1 style="margin-top:40px">{{$clinic->name}}</h1>
    


    <div class="container mt-4 mb-4">

        <div class="row">
            <div class="offset-md-3 col-md-6 col-sm-12">

                @if (Session::has('error'))
                <div class="alert alert-danger">
                    {{Session::get('error')}}
                </div>    
                @endif

                @if (Session::has('success'))
                <div class="alert alert-success">
                    {{Session::get('success')}}
                </div>    
                @endif

                <form action="/clinic/addUser/{{$clinic->slug}}" method="POST">
                    {{ csrf_field() }}
                    <input class="form-control" type="text" placeholder="會員編號" name="id_code" value="{{Session::has('id_code')?Session::get('id_code'):''}}">
                    <button class="mt-2 btn-block btn btn-success">加入志工</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="offset-md-3 col-md-6 col-sm-12">
                <hr>
                <h4>志工清單</h4>
                @foreach ($users as $i => $user)
                    <div class="user-cell p-1">
                        <button class="btn btn-sm btn-danger" onclick="removeUser('{{$user->id_code}}','{{$user->name}}')">移除</button>
                        {{$i + 1 }}.{{$user->name}}({{$user->email}})
                        <button class="float-right btn btn-sm btn-primary" onclick="doneVolunteering('{{$user->id_code}}','{{$user->name}}')">完成服務</button>
                    </div>
                @endforeach
            </div>
        </div>


        <form id="remove-form" action="/clinic/removeUser/{{$clinic->slug}}" method="POST" class="d-none">
            {{ csrf_field() }}
            <input id="remove-idCode" type="text" name="id_code">
        </form>


        <form id="done-volunteering-form" action="/clinic/doneVolunteering/{{$clinic->slug}}" method="POST" class="d-none">
            {{ csrf_field() }}
            <input id="done-idCode" type="text" name="id_code">
        </form>


        <div id="done-volunteering-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">完成服務</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5><span id="volunteer-name"></span> 志工</h5>
                        <p>完成了一次志工服務？</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="submitDoneVolunteering()">確定送出</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                    </div>
                </div>
            </div>
        </div>


        <div id="remove-user-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">移除志工</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5><span id="remove-name"></span> 志工</h5>
                        <p>確定從診所移除？</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="submitRemoveUser()">確定移除</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                    </div>
                </div>
            </div>
        </div>


    </div>


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    
    <script>

        var idCode = null;
        function removeUser(id_code,name){
            $('#remove-name').html(name);
            $('#remove-user-modal').modal('show');
            idCode = id_code;
        }

        function submitRemoveUser(){
            $('#remove-idCode').val(idCode);
            $('#remove-form').submit();
        }

        function doneVolunteering(id_code,name){
            $('#volunteer-name').html(name);
            $('#done-volunteering-modal').modal('show');
            idCode = id_code;
        }

        function submitDoneVolunteering(){
            if(idCode == null){ return; }
            $('#done-idCode').val(idCode);
            $('#done-volunteering-form').submit();
        }


    </script>
</body>
</html>