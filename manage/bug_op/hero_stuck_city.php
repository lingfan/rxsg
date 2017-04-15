<?php
//修复名将卡城池
//参数列表：无
//返回
//array[]:result
if (!defined("MANAGE_INTERFACE"))
	exit();
$ret = sql_fetch_rows("select a.hid,a.name,a.uid,a.cid,b.name as cname from sys_city_hero a,sys_city b where a.uid = 0 and a.state in (0,4) and a.npcid>0 and a.cid = b.cid and a.uid !=b.uid");
?>