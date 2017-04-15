<?php
if (!defined("MANAGE_INTERFACE"))
	exit();
if (!isset($uid)) {exit("param_not_exist");}
sql_query("delete from sys_city_hero where uid=$uid and npcid=0 and state=0");
$ret = "修复势力君主部下成功";
?>