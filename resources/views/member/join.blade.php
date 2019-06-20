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

            <form>
                
                  <div class="form-group ">
                    <label for="inputEmail4">信箱</label>
                    <input type="email" class="form-control" id="inputEmail4" placeholder="信箱">
                  </div>

                  <div class="form-group ">
                    <label for="password">密碼</label>
                    <input type="password" class="form-control" id="password" placeholder="密碼">
                  </div>
                
                <div class="form-group">
                  <label for="name">姓名</label>
                  <input type="text" class="form-control" id="name" placeholder="姓名">
                </div>

                <div class="form-group">
                  <label for="phone">電話</label>
                  <input type="text" class="form-control" id="phone" placeholder="電話">
                </div>

                <div class="form-group">
                    <label for="area">地區</label>
                    <select class="form-control" id="area">
                      <option>桃園</option>
                      <option>中壢</option>
                      <option>平鎮</option>
                      <option>大園</option>
                      <option>復興</option>
                    </select>
                  </div>
                
                
                <button type="submit" class="btn btn-secondary mt-4 btn-block">確定送出</button>
              </form>
        </div>
    </div>
</div>

    


</body>
<script src="/js/bootstrap.min.js"></script>
</html>