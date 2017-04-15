<?php
/*
	51.com网站为第三方应用程序提供的SDK开发包中的用户身份认证及加解密类
	V1.2.1	2008-09-27
*/

//define("FIVEONE_OP_API_DOMAIN", "sandbox.api");
define("FIVEONE_OP_API_DOMAIN", "api");

define("POST_TIMEOUT",300);
define("GET_TIMEOUT",300);
define("COOKIE_TIMEOUT",36000);
define("CONNECT_TIMEOUT",5);
define("READ_TIMEOUT",10);


require_once 'openapp_51.php';

$appapikey = '277b606a83ef171dfee7550cf6ce3da6';
$appsecret = '88faa8c8e17f1c7aae7f98abf75c86b0';

$OpenApp_51 = new OpenApp_51($appapikey, $appsecret);

//该函数检测用户是否登录，如果没有登录则去51网站登录，如果登录则返回当前登录的用户名；若只想得到当前登录用户名，则可以调用get_user()函数。
//$user = $OpenApp_51->require_login();
$user51 = $OpenApp_51->get_user();
$user = $OpenApp_51->get_user();
?>
