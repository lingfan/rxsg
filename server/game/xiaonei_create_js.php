<?php
	require_once("UserFunc.php");
	require_once("xiaonei/xiaonei-util.php");
	$uid = getCurrentUser(SERVER_NAME);
	$userinfo = json_encode(getUserInfo($uid));
	echo "setUserInfo($uid,$userinfo);";
	$arr = loadProvinceInfo($uid,0);
	$provinceinfo=json_encode(array('usercount'=>$arr[0],'citycount'=>$arr[1],'citytotal'=>$arr[2]));
	//echo $provinceinfo;
	echo "setProvinceInfo($provinceinfo);";
	
?>