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
                          <h1 class="display-4 pl-4 pr-4">銀髮學院會員申請</h1>
                          <p class="lead pl-4 pr-4">桃園銀髮學院成立於2015年9月</p>
                          <p class="lead pl-4 pr-4">本會主要成立宗旨為協助政府機關湍展銀髮族之權益・福利・政策與法令，並研究建議事項</p>
                        </div>
                </div>

            <form id="validate_form" action='/' method="POST">
                
                  <div class="form-group">
                    <label for="phone">手機號碼</label>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="手機號碼" required="true">
                  </div>

                  <div class="form-group ">
                    <label for="password">密碼</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="密碼" required="true">
                  </div>

                  <div class="form-group ">
                      <label for="confirm_password">確認密碼</label>
                      <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="確認密碼" required="true">
                  </div>
                
                <div class="form-group">
                  <label for="name">姓名</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="姓名" required="true">
                </div>

                <div class="form-group">
                  <label for="gender">性別</label>
                  <select class="form-control" id="gender" name="gender" required="true">
                    <option>男</option>
                    <option>女</option>
                  </select>
                </div>

                

                <div class="form-group">
                    <label for="area">地區</label>
                    <select class="form-control" id="area" name="district_id" required="true">
                      <option>桃園</option>
                      <option>中壢</option>
                      <option>平鎮</option>
                      <option>大園</option>
                      <option>復興</option>
                    </select>
                  </div>

                <div class="form-group ">
                    <label for="address">地址</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="地址" required="true">
                  </div>

                  <hr class="mt-4 mb-4">
                
                  <div class="form-group ">
                    <label for="inviter">推薦人</label>
                    <input type="text" class="form-control" id="inviter" name="inviter" placeholder="推薦人" required="true">
                  </div>

                  <div class="form-group ">
                    <label for="inviter_phone">推薦人電話</label>
                    <input type="text" class="form-control" id="inviter_phone" name="inviter_phone" placeholder="推薦人電話" required="true">
                  </div>


                <button type="submit" class="btn btn-secondary mt-4 btn-block">確定送出</button>
              </form>


        </div>
    </div>
</div>

    


</body>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.0/dist/jquery.validate.min.js"></script>
<script src="/js/validate.js"></script>
</html>