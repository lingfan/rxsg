<?php
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($uid)) {exit("param_not_exist");}
$user_armor = sql_fetch_rows("select * from sys_user_armor where uid=$uid and hid not in (select hid from sys_city_hero where uid=$uid )");
if (!empty($user_armor)){
	foreach ($user_armor as $sid){
		sql_query("update sys_user_armor set hid='0' where sid='$sid[sid]'");
	}
}
$ret ="修复成功";
?>
