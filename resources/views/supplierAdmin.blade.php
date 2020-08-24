<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <link rel="stylesheet" href="{{asset('css/supplierApp.css')}}">
    <title>銀髮學院-廠商後台</title>
</head>
<body>
    <div id="app">
        <app-home></app-home>
        {{-- <app-login></app-login> --}}
    </div>
</body>
<script src="{{asset('js/supplierApp.js')}}"></script>
</html>