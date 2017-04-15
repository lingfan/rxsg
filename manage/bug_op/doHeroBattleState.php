<?php
//恢复名将卡野地
//参数列表：无
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();
sql_query("update sys_city_hero set state=0 where state=3 and npcid>0 and hid not in (select hid from sys_troops) limit 100");
$ret = "名将恢复成功 ！";
?>