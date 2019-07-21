<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <title>申請入會</title>
    <style>
        body{
            padding: 40px 0 ;
        }
        form label.error{
            color: #dc3545;
        }
        form input.error,form textarea.error{
            border:2px solid #dc3545;
        }


        form input.valid{
            border:2px solid #28a745;
        }


    </style>
</head>
<body>
    
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2 col-xs-12">

                <div class="jumbotron jumbotron-fluid">
                        <div class="container">
                          <h1 class="display-4 pl-4 pr-4">桃園市銀髮族協會</h1>
                          <p class="lead pl-4 pr-4">桃園銀髮學院成立於2015年9月</p>
                          <p class="lead pl-4 pr-4">本會主要成立宗旨為協助政府機關湍展銀髮族之權益・福利・政策與法令，並研究建議事項</p>
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

            <form id="validate_form" action='/member/join' method="POST">
                {{csrf_field()}}
                  <div class="form-group">
                    <label for="phone">手機號碼</label>
                  <input type="text" class="form-control" id="email" name="email" placeholder="手機號碼" required="true" value="{{old('email')}}">
                  </div>

                  <div class="form-group ">
                    <label for="password">密碼</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="密碼" required="true" >
                  </div>

                  <div class="form-group ">
                      <label for="confirm_password">確認密碼</label>
                      <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="確認密碼" required="true" >
                  </div>
                
                <div class="form-group">
                  <label for="name">姓名</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="姓名" required="true" value="{{old('name')}}">
                </div>

                <div class="form-group">
                  <label for="gender">性別</label>
                  <select class="form-control" id="gender" name="gender" required="true" >
                    <option value='1'>男</option>
                    <option value='0'>女</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="birthdate">出生年月日</label>
                  <input type="date" class="form-control" id="birthdate" name="birthdate" placeholder="出生年月日" required="true" value="{{old('birthdate')}}">
                </div>

                <div class="form-group">
                  <label for="id_number">身分證字號</label>
                  <input type="text" class="form-control" id="id_number" name="id_number" placeholder="身分證字號" required="true" value="{{old('id_number')}}">
                </div>

                <div class="form-group">
                    <label for="area">地區</label>
                    <select class="form-control" id="area" name="district_id" required="true" >
                      <option value="1">桃園</option>
                      <option value="2">中壢</option>
                      <option value="3">平鎮</option>
                      <option value="4">大園</option>
                      <option value="5">復興</option>
                    </select>
                  </div>

                <div class="form-group ">
                    <label for="address">地址</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="地址" required="true" value="{{old('address')}}">
                  </div>

                  <hr class="mt-4 mb-4">
                
                  <div class="form-group ">
                    <label for="inviter">推薦人</label>
                    <input type="text" class="form-control" id="inviter" name="inviter" placeholder="推薦人" required="true" value="{{old('inviter')}}">
                  </div>

                  <div class="form-group ">
                    <label for="inviter_phone">推薦人電話</label>
                    <input type="text" class="form-control" id="inviter_phone" name="inviter_phone" placeholder="推薦人電話" required="true" value="{{old('inviter_phone')}}">
                  </div>

                  <div class="form-group">
                    <label for="pay_method">繳費方式</label>
                    <select class="form-control" id="pay_method" name="pay_method" required="true" >
                      <option value=''>請選擇繳費方式</option>
                      <option value='1'>推薦人代收(禮品由代收人負責交付)</option>
                      <option value='0'>自行繳費(自行領取禮品)</option>
                    </select>
                  </div>


                <div onclick="inviter_check();" class="btn btn-secondary mt-4 btn-block">確定送出</div>
              </form>


        </div>
    </div>
</div>

    


</body>
{{-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script> --}}
<script
  src="https://code.jquery.com/jquery-3.4.1.min.js"
  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
  crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.0/dist/jquery.validate.min.js"></script>
<script>
var gender = '{{ old('gender') }}';
var district_id = '{{ old('district_id') }}';
$(document).ready(function(){
  if(gender){
    $('[name="gender"]').val(gender);
  }
  if (district_id) {
    $('[name="district_id"]').val(district_id);  
  }
});

function inviter_check(){
  $.ajax({
			type:'GET',
			url: '/api/inviterCheck',
      dataType:'json',
      data:{
        'inviter':$('[name="inviter"]').val(),
        'inviter_phone':$('[name="inviter_phone"]').val(),
      },
			success: function (response) {
        if(response.s == 1){
          $('#validate_form').submit();
        }else if(response.s == 0){
          alert('對應此聯絡電話的用戶並不存在，請檢查是否輸入錯誤或向推薦人確認。')
        }
        console.log(response);
      },
      error: function (error) {
        console.log(error);
        // alert('錯誤');
      },
		});
}

$().ready(function() {
    $("#validate_form").validate({
      rules:{
        confirm_password:{
          equalTo:'#password',
        }
      }
    });
});

jQuery.extend(jQuery.validator.messages, {
    required: "這是必填欄位",
    email: "email格式錯誤",
    maxlength: jQuery.validator.format("最多{0}碼"),
    minlength: jQuery.validator.format("最少{0}碼"),
    max: jQuery.validator.format("最多為{0}"),
    min: jQuery.validator.format("最少為{0}"),
    equalTo:jQuery.validator.format("與密碼不相符"),
  });
</script>
</html>