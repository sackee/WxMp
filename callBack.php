<?php
require_once dirname(__FILE__)."/sdk/WxAuth.php";
$wx = new WxAuth();

$code = I('code');
$backUrl = base64_decode(I('back_url'));
$state = I('state');
$res = $wx->weixinUserInfo($code ,$state);
header("Location:".$backUrl);
exit;
?>
