<?php
require_once dirname(__FILE__)."/sdk/WxAuth.php";

$uri=$_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
$backUrl  =   'http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')+1)."callBack.php";

$wx = new WxAuth(['callBackUrl' => $backUrl]);

$session_id = $_COOKIE['wxlogin_session_id'];
if(!$session_id){
    session_start();
    $session_id = session_id();
    setcookie('wxlogin_session_id', $session_id, time()+2592000, '/', $_SERVER['HTTP_HOST']);
}

print_r($wx->getUserInfo($session_id, WxHelper::getUrl()));

?>
