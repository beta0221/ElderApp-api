<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <title>銀髮學院-請求刪除使用者資料</title>
    
</head>

<body>

    <div class="container mt-4 mb-4">

        <div class="row">
            <div class="col-md-8 offset-md-2 col-xs-12">
                <h4>銀髮學院</h3>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-md-2 col-xs-12">

                <div class="jumbotron jumbotron-fluid">
                    <div class="container">
                        <h1 class="display-4 pl-4 pr-4">刪除使用者資料</h1>
                        <p class="lead pl-4 pr-4">請填寫您所註冊的會員資料</p>
                        <p class="lead pl-4 pr-4">若經比對無誤，我們會將此帳號資料加入刪除排程。</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="validate_form" action='/member/delete' method="POST">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label for="phone">會員帳號</label>
                        <input type="text" class="form-control" id="email" name="email" placeholder="會員帳號"
                            required="true" value="{{ old('email') }}">
                    </div>

                    <div class="form-group">
                        <label for="name">姓名</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="姓名"
                            required="true" value="{{ old('name') }}">
                    </div>

                    <div class="form-group">
                        <label for="phone">手機號碼</label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="手機號碼"
                            value="{{ old('phone') }}">
                    </div>

                    <div class="form-group">
                        <label for="birthdate">出生年月日</label>
                        <input type="date" class="form-control" id="birthdate" name="birthdate" placeholder="出生年月日"
                            required="true" value="{{ old('birthdate') }}">
                    </div>

                    <button type="submit" class="btn btn-secondary mt-4 btn-block">確定送出</button>
                </form>

            </div>
        </div>
    </div>




</body>

<script src="/js/bootstrap.min.js"></script>

<script></script>

</html>
