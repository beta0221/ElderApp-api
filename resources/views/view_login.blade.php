<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form action="/web_login" method="POST">
        {{ csrf_field() }}
        <input type="text" value="beta0221" name="email">
        <input type="text" value="0221" name="password">
        <button type="submit">登入</button>
    </form>
</body>
</html>