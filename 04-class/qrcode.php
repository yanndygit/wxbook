<?php  header("Content-Type:text/html;   charset=utf-8"); include("./phpqrcode/phpqrcode.php"); ?>
<html>
<head>
<style type="text/css">
.title{
 font-size: 200%;
 text-align:center; 
}
.bottom{
 font-size: 100%;
 text-align:center; 
   
}

.qr_image{
text-align:center;
}

</style>
</head>
<body>

<?php
$isbn13 = 'EAN_13,9787540438845';
# filename
$src='qrcode_'.'_'.$isbn13.'.png';	
QRcode::png($isbn13,$src);
?>
<div class='title'><?php echo '生成二维码';?> </div> 
<div class="qr_image">
<img width="320" height="320" border="0" id="detailImg" src="<?php echo $src?>"/>
</div>
<div class='bottom'><?php  echo '扫一扫';?>  </div> 
</body>
</html>
