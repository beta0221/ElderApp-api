<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/bootstrap.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <title>組織人員</title>
</head>

<?php
    $rank = [
        1=>'主人',
        2=>'小天使',
        3=>'大天使',
        4=>'守護天使',
        5=>'領航天使'
    ];
?>
<body>
    <div class="container">

        <h2 class="mt-2 mb-2">組織人員表</h2>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                      <tr>
                        <th scope="col">#</th>
                        <th scope="col">姓名</th>
                        <th scope="col">身分證</th>
                        <th scope="col">生日</th>
                        <th scope="col">電話</th>
                        <th scope="col">地區</th>
                        <th scope="col">會員效期</th>
                        <th scope="col">會員資格</th>
                        <th scope="col">職位</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $i => $user)
                            <tr>
                                <th scope="row">{{($i+1)}}</th>
                                <td>{{$user->name}}</td>
                                <td>{{$user->id_number}}</td>
                                <td>{{$user->birthdate}}</td>
                                <td>{{$user->phone}}</td>
                                <td>{{$districtDict[$user->district_id]}}</td>
                                <td>{{$user->expiry_date}}</td>
                                <td class="{{($user->valid)?'text-success':'text-danger'}}">{{($user->valid)?'有效':'無效'}}</td>
                                <td>{{(isset($rank[$user->org_rank]))?$rank[$user->org_rank]:'主人'}}</td>
                            </tr>   
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>