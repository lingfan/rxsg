<?php
//查找需要修复双城守问题的城市
//参数列表：无
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();
sql_query("update sys_city_hero h set state=0 where state=1 and hid not in (select chiefhid from sys_city c where c.cid=h.cid)");
sql_query("update sys_city_hero h set state=1 where state=0 and hid in (select chiefhid from sys_city c where c.cid=h.cid)");
$ret = "修复成功 ！";
?>