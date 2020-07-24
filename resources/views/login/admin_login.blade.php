<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/bootstrap.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>登入</title>
    <style>
        label{
            line-height: 38px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3 col-sm-12">
                    <div class="login-form p-4">
                
                        <form action="/web_admin_login" method="POST" role="form" class="text-center">
                            {{ csrf_field() }}
                            <h2 class="mb-4">會員登入</h2>
                
                            <input type="hidden" name="from" value="{{isset($from)?$from:''}}">
                
                            <div class="form-row mb-2">
                                <div class="col-3">
                                    <label class="control-label m-0">帳號 ：</label>
                                </div>
                                <div class="col-9">
                                    <input class="form-control" type="text" placeholder="請輸入帳號" name="email" value="{{(isset($email))?$email:''}}">
                                </div>
                            </div>
                
                            <div class="form-row mb-2">
                                <div class="col-3">
                                    <label class="control-label">密碼 ：</label>
                                </div>
                                
                                <div class="col-9">
                                    <input class="form-control" type="password" placeholder="請輸入密碼" name="password" value="{{(isset($password))?$password:''}}">
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <button class="btn btn-block btn-primary">登入</button>
                            </div>
                
                            @if (isset($error))
                            <div>
                                <span class="text-danger">{{$error}}</span>
                            </div>    
                            @endif
                            
                
                        </form>
                
                    </div>
                </div>
            </div>
        </div>
        
    </div>

</body>
</html>