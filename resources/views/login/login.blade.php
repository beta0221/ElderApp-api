@extends('main')

@section('title','| 會員登入')

@section('css')
<style>
    .login-wrap {
        min-height: 480px;
        width: 100%;
        margin: 0 auto;
        /* margin-top:150px; */
        /* border:1px solid #eee; */
        background: #ffffff;
        border-radius: 8px;

    }

    .login-form {
        width: 100%;
        height: 200px;
        position: absolute;
        top: 40%;
        transform: translateY(-50%);
    }

 
    .btn {
        /* border-radius: 8px; */
    }

    label{
        line-height: 38px;
    }

</style>
@endsection

@section('content')
<div class="login-wrap">

    <div class="login-form p-4">
        
        <form action="/web_login" method="POST" role="form" class="text-center">
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
@endsection


@section('js')
@endsection
