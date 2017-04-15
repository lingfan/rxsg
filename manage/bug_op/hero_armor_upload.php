<?php
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
    if (!isset($hid) && !empty($hid)) {exit("param_not_exist");}
	sql_query("delete from sys_hero_armor where hid='$hid' limit 16");
	sql_query("update sys_user_armor set hid=0 where hid='$hid' limit 16");
	$ret = "装备成功卸下";
?>