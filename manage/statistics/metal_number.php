<?php
	//勋章获得量统计（多服）
	//参数列表：
	//$startday:开始日期
	//$endday:结束日期
	//返回
	//array[0]:array{cao,huang,huang}
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	
	$ret = sql_fetch_one("select sum(case when battleid='1001' then metal else 0 end) as huang,
											sum(case when battleid='2001' and unionid=3 then metal else 0 end) as yuan,
											sum(case when battleid='2001' and unionid=4 then metal else 0 end) as cao
											from log_battle_honour 
											where starttime between  unix_timestamp('$startday') and unix_timestamp('$endday')+86400"); 
?>