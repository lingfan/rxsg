<?php
include("xiaonei/xiaonei-util.php");
//TODO 取得服务器名字作为trace
$currentUser = getCurrentUser(SERVER_NAME);
//$currentUser="xiaoneiTest1";
if($currentUser!=""){
	$passport = $currentUser;
	$passsucc = true;
} else {
	$passsucc = false;
}
?>