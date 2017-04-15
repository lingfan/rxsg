<?php
//列表卡住的战斗
//参数列表：无
//返回
//array
if (!defined("MANAGE_INTERFACE")) exit();

	$ret['1']=sql_fetch_rows("select distinct targetcid from sys_troops where state=3 and battleid not in ( select id from mem_battle)");
	$ret['2']=sql_fetch_rows("select distinct(targetcid) from sys_troops where task = 5 and state = 1 and endtime < unix_timestamp()-86400 and battleid not in (select id from mem_battle )");
	
?>