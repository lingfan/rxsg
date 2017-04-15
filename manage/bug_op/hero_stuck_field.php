<?php
//修复名将卡野地
//参数列表：无
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();
$ret = sql_fetch_rows("select * from sys_city_hero h where h.uid = 895 and h.hid <1000 and h.state !=4 and h.cid not in (select cid from sys_city where cid=h.cid)");
?>