<!DOCTYPE html>
<html>
<head>
<title>領取活動獎勵 | {{$event->title}}</title>
<style>
    body{
        text-align: center;
    }
</style>
</head>
<body>

<h1 style="margin-top:40px">領取活動獎勵：{{$event->title}}</h1>
<h3 style="margin-bottom:80px;">請掃描下方 QR Code 領取活動獎勵。</h3>
<div id="output"></div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script type="text/javascript" src="/js/jquery.qrcode.min.js"></script>
<script>
jQuery(function(){
	jQuery('#output').qrcode("{{$event->slug}}");
})
</script>

</body>
</html>