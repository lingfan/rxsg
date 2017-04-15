<?php
//恢复名将卡野地
//参数列表：无
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();
sql_query("update sys_city_hero h set h.uid=0, h.state = 4 where h.uid = 895 and h.hid <1000 and h.state !=4 and h.cid not in (select cid from sys_city where cid=h.cid)");
$ret = "名将恢复成功 ！";
?>