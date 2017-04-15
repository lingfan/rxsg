<?php
//修复卡交易0秒
//参数列表：无
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();
sql_query("insert into mem_city_trade (select id,endtime from sys_city_trade where state=1 and id not in (select id from mem_city_trade))");
$ret = "修复成功 ！";
?>