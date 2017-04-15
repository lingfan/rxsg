<?php

/*
 * 
 * 家园平台接口验证
 * 
 */



if (!defined('PATH_SEPARATOR')) {if (substr(PHP_OS, 0, 3) == 'WIN') define('PATH_SEPARATOR', ';'); else define('PATH_SEPARATOR', ':');}	 
//设置根目录绝对路径到include_path,简化path的使用
ini_set('include_path',ini_get('include_path').PATH_SEPARATOR.realpath("../../../").PATH_SEPARATOR.realpath("../../../lib"));
require_once("config/db.php");
require_once("DB.php");
require_once("mysql.php");
require_once("database.php"); 

//$passport=!empty($_GET["passport"])?addslashes($_GET["passport"]):0;
$passport=isset($_GET["passport"])?$_GET["passport"]:$_POST["Passport"];
$time=isset($_GET["time"])?$_GET["time"]:$_POST["Time"];
$sign=isset($_GET["sign"])?$_GET["sign"]:$_POST["Sign"];
$key="M7MDFCR9WRRGRQ3ETBQ6";

if($sign!=md5($time.$key)){
	echo "validate_fault";
	exit;
}

if($passport=="")
	$passport=0;

//$passport='d';
if($passport==='0'){
	$uid=-1;
}else{
	$uid=sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
	if(empty($uid))
		$uid=-1;
}


?>