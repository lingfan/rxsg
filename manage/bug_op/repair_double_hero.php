<?php
//查找需要修复双主将和军师
//参数列表：无
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();
sql_query("update sys_city_hero h set state=0 where state=8 and hid not in (select counsellorid from sys_city c where c.cid=h.cid);");
sql_query("update sys_city_hero h set state=8 where state=0 and hid in (select counsellorid from sys_city c where c.cid=h.cid);");
sql_query("update sys_city_hero h set state=0 where state=7 and hid not in (select generalid from sys_city c where c.cid=h.cid);");
sql_query("update sys_city_hero h set state=7 where state=0 and hid in (select generalid from sys_city c where c.cid=h.cid);");
$ret = "修复成功 ！";
?>