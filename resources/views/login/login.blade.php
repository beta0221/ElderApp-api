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
        
        <form action="" role="form" class="text-center">
        <h2 class="mb-4">會員登入</h2>

        <div class="form-row mb-2">

            <div class="col-2">
                <label for="" class="control-label m-0">帳號 ：</label>
            </div>
            <div class="col-10">
                <input class="form-control" type="text" placeholder="請輸入帳號">
            </div>

        </div>

         <div class="form-row mb-2">
            <div class="col-2">
                <label for="" class="control-label">密碼 ：</label>
            </div>
            
            <div class="col-10">
                <input class="form-control" type="password" placeholder="請輸入密碼">
            </div>
        </div>

            <div>
                <button class="btn btn-block btn-primary">登入</button>
            </div>
        </form>
    </div>



</div>
@endsection


@section('js')
@endsection
