<?php
	//活跃玩家数，有过登录记录的
	//参数列表：
	//startday:开始日期
	//endday:结束日期
	//返回
	//array[0]:count
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($startday)){exit("param_not_exist");}
	if (!isset($endday)){exit("param_not_exist");}
	
	$ret[] = sql_fetch_one_cell("select count(distinct(uid)) as count from log_login where time >= unix_timestamp($startday) and time < unix_timestamp($endday)+86400");
?>